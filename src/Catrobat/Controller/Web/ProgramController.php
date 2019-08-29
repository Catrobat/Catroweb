<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Catrobat\Services\RudeWordFilter;
use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\StatisticsService;
use App\Catrobat\Services\TestEnv\FakeStatisticsService;
use App\Catrobat\StatusCode;
use App\Entity\LikeNotification;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\RemixManager;
use App\Entity\User;
use App\Entity\UserComment;
use App\Repository\CatroNotificationRepository;
use App\Repository\FeaturedRepository;
use App\Repository\GameJamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Error\Error;


/**
 * Class ProgramController
 * @package App\Catrobat\Controller\Web
 */
class ProgramController extends AbstractController
{

  /**
   * @var StatisticsService|FakeStatisticsService $statistics
   */
  private $statistics;

  /**
   * ProgramController constructor.
   *
   * @param StatisticsService $statistics_service
   */
  public function __construct(StatisticsService $statistics_service)
  {
    $this->statistics = $statistics_service;
  }

  /**
   * @Route("/project/remixgraph/{id}", name="program_remix_graph", methods={"GET"})
   *
   * @param Request $request
   * @param $id
   * @param RemixManager $remix_manager
   * @param ScreenshotRepository $screenshot_repository
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function programRemixGraphAction(Request $request, $id, RemixManager $remix_manager,
                                          ScreenshotRepository $screenshot_repository)
  {
    $remix_graph_data = $remix_manager->getFullRemixGraph($id);

    $catrobat_program_thumbnails = [];
    foreach ($remix_graph_data['catrobatNodes'] as $node_id)
    {
      if (!array_key_exists($node_id, $remix_graph_data['catrobatNodesData']))
      {
        $catrobat_program_thumbnails[$node_id] = '/images/default/not_available.png';
        continue;
      }
      $catrobat_program_thumbnails[$node_id] = '/' . $screenshot_repository
          ->getThumbnailWebPath($node_id);
    }

    $locale = strtolower($request->getLocale());
    $referrer = $request->headers->get('referer');
    $this->statistics->createClickStatistics($request, 'show_remix_graph', 0, $id, null,
      null, $referrer, $locale, false, false);

    return new JsonResponse([
      'id'                        => $id,
      'remixGraph'                => $remix_graph_data,
      'catrobatProgramThumbnails' => $catrobat_program_thumbnails,
    ]);
  }


  /**
   * @Route("/project/{id}", name="program")
   * @Route("/details/{id}", name="catrobat_web_detail", methods={"GET"})
   *
   * @param Request $request
   * @param $id
   * @param ProgramManager $program_manager
   * @param FeaturedRepository $featured_repository
   * @param ScreenshotRepository $screenshot_repository
   * @param ElapsedTimeStringFormatter $elapsed_time
   * @param AppRequest $app_request
   * @param RemixManager $remix_manager
   * @param GameJamRepository $game_jam_repository
   *
   * @return Response
   * @throws Error
   * @throws NonUniqueResultException
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function programAction(Request $request, $id, ProgramManager $program_manager,
                                FeaturedRepository $featured_repository, ScreenshotRepository $screenshot_repository,
                                ElapsedTimeStringFormatter $elapsed_time, AppRequest $app_request,
                                RemixManager $remix_manager, GameJamRepository $game_jam_repository)
  {
    /**
     * @var $user             User
     * @var $program          Program
     * @var $reported_program ProgramInappropriateReport
     * @var $like             ProgramLike
     */
    $program = $program_manager->find($id);
    $router = $this->get('router');

    if (!$program || !$program->isVisible())
    {
      if (!$featured_repository->isFeatured($program))
      {
        throw $this->createNotFoundException('Unable to find Project entity.');
      }
    }

//    Right now everyone should find even private programs via the correct link! SHARE-49
//    if ($program->getPrivate() && $program->getUser()->getId() !== $this->getUser()->getId()) {
//      // only program owners should be allowed to see their programs
//      throw $this->createNotFoundException('Unable to find Project entity.');
//    }

    if ($program->isDebugBuild())
    {
      if (!$app_request->isDebugBuildRequest())
      {
        throw $this->createNotFoundException('Unable to find Project entity.');
      }
    }

    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $program, $viewed, $program_manager);
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);

    $user = $this->getUser();
    $logged_in = false;
    $user_name = "";
    $like_type = ProgramLike::TYPE_NONE;
    $like_type_count = 0;

    if ($user !== null)
    {
      $logged_in = true;
      $user_name = $user->getUsername();
      $like = $program_manager->findUserLike($program->getId(), $user->getId());
      if ($like !== null)
      {
        $like_type = $like->getType();
        $like_type_count = $program_manager->likeTypeCount($program->getId(), $like_type);
      }
    }

    $total_like_count = $program_manager->totalLikeCount($program->getId());
    $program_comments = $this->findCommentsById($program);
    $program_details = $this->createProgramDetailsArray($screenshot_repository, $program,
      $like_type, $like_type_count, $total_like_count, $elapsed_time, $referrer,
      $program_comments, $request,$remix_manager);

    $user_programs = $this->findUserPrograms($user, $program);

    $isReportedByUser = $this->checkReportedByUser($program, $user);

    $program_url = $this->generateUrl('program',
      ['id' => $program->getId()], true);
    $share_text = trim($program->getName() . ' on ' . $program_url . ' ' .
      $program->getDescription());

    $jam = $this->extractGameJamConfig($game_jam_repository);

    $max_description_size = $this->getParameter("catrobat.max_description_upload_size");

    $my_program = false;
    if ($user_programs && count($user_programs) > 0)
    {
      $my_program = true;
    }

    return $this->get('templating')->renderResponse('Program/program.html.twig', [
      'program_details_url_template' => $router->generate('program', ['id' => 0]),
      'program'                      => $program,
      'program_details'              => $program_details,
      'my_program'                   => $my_program,
      'already_reported'             => $isReportedByUser,
      'shareText'                    => $share_text,
      'program_url'                  => $program_url,
      'jam'                          => $jam,
      'user_name'                    => $user_name,
      'max_description_size'         => $max_description_size,
      'logged_in'                    => $logged_in,
    ]);
  }


  /**
   * @Route("/project/like/{id}", name="program_like", methods={"GET"})
   *
   * @param Request $request
   * @param GuidType $id
   * @param ProgramManager $program_manager
   * @param CatroNotificationRepository $notification_repo
   * @param CatroNotificationService $notification_service
   *
   * @return JsonResponse|RedirectResponse
   * @throws Exception
   */
  public function programLikeAction(Request $request, $id, ProgramManager $program_manager,
                                    CatroNotificationRepository $notification_repo,
                                    CatroNotificationService  $notification_service )
  {
    /**
     * @var User                     $user
     * @var Program                  $program
     */

    $type = intval($request->query->get('type', ProgramLike::TYPE_THUMBS_UP));
    $no_unlike = (bool)$request->query->get('no_unlike', false);

    if (!ProgramLike::isValidType($type))
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create(['statusCode' => StatusCode::INVALID_PARAM,
                                     'message'    => 'Invalid like type given!']);
      }
      else
      {
        throw $this->createAccessDeniedException('Invalid like-type for program given!');
      }
    }
    $program = $program_manager->find($id);
    if ($program === null)
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create(['statusCode' => StatusCode::INVALID_PARAM,
                                     'message'    => 'Program with given ID does not exist!']);
      }
      else
      {
        throw $this->createNotFoundException('Program with given ID does not exist!');
      }
    }

    $user = $this->getUser();
    if (!$user)
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
      }
      else
      {
        $request->getSession()->set('catroweb_login_redirect', $this->generateUrl(
          'program_like', ['id' => $id, 'type' => $type, 'no_unlike' => 1]));

        return $this->redirectToRoute('login');
      }
    }

    $new_type = $program_manager->toggleLike($program, $user, $type, $no_unlike);
    $like_type_count = $program_manager->likeTypeCount($program->getId(), $type);
    $total_like_count = $program_manager->totalLikeCount($program->getId());

    if ($program->getUser() !== $user)
    {
      $program_notification_exists = false;

      $notifications = $notification_repo->findByUser($program->getUser());

      foreach ($notifications as $notification)
      {
        if ($notification instanceof LikeNotification)
        {
          if ($notification->getLikeFrom()->getId() === $user->getId() &&
            $notification->getProgram()->getId() === $program->getId())
          {
            $program_notification_exists = true;
            break;
          }
        }
      }

      if ($new_type === ProgramLike::TYPE_THUMBS_UP
        || $new_type === ProgramLike::TYPE_SMILE
        || $new_type === ProgramLike::TYPE_LOVE
        || $new_type === ProgramLike::TYPE_WOW
      )
      {
        if (!$program_notification_exists)
        {
          $notification = new LikeNotification($program->getUser(), $user, $program);
          $notification_service->addNotification($notification);
        }
      }
      else
      {
        if ($program_notification_exists)
        {
          $notification_service->removeNotification($notification);
        }
      }
    }

    if (!$request->isXmlHttpRequest())
    {
      return $this->redirectToRoute('program', ['id' => $id]);
    }

    return new JsonResponse(['statusCode' => StatusCode::OK, 'data' => [
      'id'             => $id,
      'likeType'       => $new_type,
      'likeTypeCount'  => $like_type_count,
      'totalLikeCount' => $total_like_count,
    ]]);
  }


  /**
   * @Route("/search/{q}", name="search", requirements={"q":".+"}, methods={"GET"})
   * @Route("/search/", name="empty_search", defaults={"q":null}, methods={"GET"})
   *
   * @param string $q
   *
   * @return JsonResponse|RedirectResponse
   * @throws Exception
   *
   */
  public function searchAction($q)
  {
    return $this->get('templating')->renderResponse('Search/search.html.twig', ['q' => $q]);
  }


  /**
   * @Route("/userDeleteProject/{id}", name="profile_delete_program", defaults={"id" = 0}, methods={"GET"})
   *
   * @param GuidType $id
   *
   * @return JsonResponse|RedirectResponse
   * @throws Exception
   *
   */
  public function deleteProgramAction($id)
  {
    /**
     * @var $user          User
     * @var $program       Program
     * @var $user_programs ArrayCollection
     */
    if ($id === 0)
    {
      return $this->redirectToRoute('profile');
    }

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $user_programs = $user->getPrograms();
    $programs = $user_programs->matching(Criteria::create()
      ->where(Criteria::expr()->eq('id', $id)));

    $program = $programs[0];
    if (!$program)
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $program->setVisible(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    return $this->redirectToRoute('profile');
  }


  /**
   * @Route("/userToggleProjectVisibility/{id}", name="profile_toggle_program_visibility",
   *   defaults={"id" = 0}, methods={"GET"})
   *
   * @param GuidType $id
   *
   * @return Response
   * @throws Exception
   *
   */
  public function toggleProgramVisibilityAction($id)
  {
    /**
     * @var $user          User
     * @var $program       Program
     * @var $user_programs ArrayCollection
     */

    if ($id === 0)
    {
      return new Response("false");
    }

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $user_programs = $user->getPrograms();
    $programs = $user_programs->matching(Criteria::create()
      ->where(Criteria::expr()->eq('id', $id)));

    $program = $programs[0];

    if (!$program)
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $program->setPrivate(!$program->getPrivate());

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    return new Response("true");
  }


  /**
   * @Route("/editProjectDescription/{id}/{newDescription}", name="edit_program_description",
   *   options={"expose"=true}, methods={"GET"})
   *
   * @param GuidType $id
   * @param string  $newDescription
   * @param RudeWordFilter  $rude_word_filter
   * @param ProgramManager  $program_manager
   * @param TranslatorInterface  $translator
   *
   * @return Response
   * @throws Exception
   *
   */
  public function editProgramDescription($id, $newDescription, RudeWordFilter $rude_word_filter,
                                         ProgramManager $program_manager, TranslatorInterface $translator)
  {
    /**
     * @var User           $user
     * @var Program        $program
     */

    $max_description_size = $this->getParameter("catrobat.max_description_upload_size");

    if (strlen($newDescription) > $max_description_size)
    {
      return JsonResponse::create(['statusCode' => StatusCode::DESCRIPTION_TOO_LONG,
                                   'message'    => $translator
                                     ->trans("programs.tooLongDescription", [], "catroweb")]);
    }

    if ($rude_word_filter->containsRudeWord($newDescription))
    {
      return JsonResponse::create(['statusCode' => StatusCode::RUDE_WORD_IN_DESCRIPTION,
                                   'message'    => $translator
                                     ->trans("programs.rudeWordsInDescription", [], "catroweb")]);
    }

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $program = $program_manager->find($id);
    if (!$program)
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    if ($program->getUser() !== $user)
    {
      throw $this->createAccessDeniedException('Not your program!');
    }

    $program->setDescription($newDescription);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    return JsonResponse::create(['statusCode' => StatusCode::OK]);
  }


  /**
   * @param GameJamRepository $game_jam_repository
   *
   * @return array|null
   * @throws NonUniqueResultException
   */
  private function extractGameJamConfig(GameJamRepository $game_jam_repository)
  {
    $jam = null;

    $gamejam = $game_jam_repository->getCurrentGameJam();

    if ($gamejam)
    {
      $gamejam_flavor = $gamejam->getFlavor();
      if ($gamejam_flavor !== null)
      {
        $config = $this->getParameter('gamejam');
        $gamejam_config = $config[$gamejam_flavor];
        if ($gamejam_config)
        {
          $logo_url = $gamejam_config['logo_url'];
          $display_name = $gamejam_config['display_name'];
          $gamejam_url = $gamejam_config['gamejam_url'];
          $jam = [
            'name'        => $display_name,
            'logo_url'    => $logo_url,
            'gamejam_url' => $gamejam_url,
          ];
        }
      }
    }

    return $jam;
  }


  /**
   * @param Request $request
   * @param Program $program
   * @param         $viewed
   * @param ProgramManager $program_manager
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  private function checkAndAddViewed(Request $request, $program, $viewed, ProgramManager $program_manager)
  {
    if (!in_array($program->getId(), $viewed))
    {
      $program_manager->increaseViews($program);
      $viewed[] = $program->getId();
      $request->getSession()->set('viewed', $viewed);
    }
  }


  /**
   * @param $screenshot_repository      ScreenshotRepository
   * @param $program                    Program
   * @param $elapsed_time               ElapsedTimeStringFormatter
   * @param $referrer
   * @param $like_type
   * @param $like_type_count
   * @param $total_like_count
   * @param $program_comments
   * @param $request                    Request
   * @param $remix_manager              RemixManager
   *
   * @return array
   */
  private function createProgramDetailsArray($screenshot_repository, $program, $like_type,
                                             $like_type_count, $total_like_count, $elapsed_time,
                                             $referrer, $program_comments, $request, $remix_manager)
  {
    $rec_by_page_id = intval($request->query->get('rec_by_page_id', RecommendedPageId::INVALID_PAGE));
    $rec_by_program_id = intval($request->query->get('rec_by_program_id', 0));
    $rec_user_specific = intval($request->query->get('rec_user_specific', 0));
    $rec_tag_by_program_id = intval($request->query->get('rec_from', 0));

    if (RecommendedPageId::isValidRecommendedPageId($rec_by_page_id))
    {
      // all recommendations should generate this download link!
      // (except tag-recommendations -> see below)
      // At the moment only recommendations based on remixes are supported!
      $url = $this->generateUrl('download', [
        'id'                => $program->getId(),
        'rec_by_page_id'    => $rec_by_page_id,
        'rec_by_program_id' => $rec_by_program_id,
        'rec_user_specific' => $rec_user_specific,
        'fname'             => $program->getName(),
      ]);
    }
    else
    {
      if ($rec_tag_by_program_id > 0)
      {
        // tag-recommendations should generate this download link!
        $url = $this->generateUrl('download', [
          'id'       => $program->getId(),
          'rec_from' => $rec_tag_by_program_id,
          'fname'    => $program->getName(),
        ]);
      }
      else
      {
        // case: NO recommendation
        $url = $this->generateUrl('download', ['id' => $program->getId(), 'fname' => $program->getName()]);
      }
    }

    $comments_avatars = [];
    foreach ($program_comments as $comment)
    {
      /**
       * @var   $em      EntityManager
       * @var   $comment UserComment
       */
      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository(User::class)->findOneBy([
        'id' => $comment->getUserId(),
      ]);
      if ($user !== null)
      {
        $avatar = $user->getAvatar();
        if ($avatar)
        {
          $comments_avatars[$comment->getId()] = $avatar;
        }
      }
    }

    $program_details = [
      'screenshotBig'   => $screenshot_repository->getScreenshotWebPath($program->getId()),
      'downloadUrl'     => $url,
      'languageVersion' => $program->getLanguageVersion(),
      'downloads'       => $program->getDownloads() + $program->getApkDownloads(),
      'views'           => $program->getViews(),
      'filesize'        => sprintf('%.2f', $program->getFilesize() / 1048576),
      'age'             => $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp()),
      'referrer'        => $referrer,
      'id'              => $program->getId(),
      'comments'        => $program_comments,
      'commentsLength'  => count($program_comments),
      'commentsAvatars' => $comments_avatars,
      'remixesLength'   => $remix_manager->remixCount($program->getId()),
      'likeType'        => $like_type,
      'likeTypeCount'   => $like_type_count,
      'totalLikeCount'  => $total_like_count,
      'isAdmin'         => $this->isGranted("ROLE_ADMIN"),
    ];

    return $program_details;
  }


  /**
   * @param $program Program
   *
   * @return array|UserComment[]
   */
  private function findCommentsById($program)
  {
    $program_comments = $this->getDoctrine()
      ->getRepository('App\Entity\UserComment')
      ->findBy(
        ['programId' => $program->getId()], ['id' => 'DESC']);

    return $program_comments;
  }


  /**
   * @param $user    User
   * @param $program Program
   *
   * @return null
   */
  private function findUserPrograms($user, $program)
  {
    /**
     * @var $programs ArrayCollection
     */
    $user_programs = null;
    if ($user)
    {
      $programs = $user->getPrograms();
      $user_programs = $programs->matching(Criteria::create()
        ->where(Criteria::expr()->eq('id', $program->getId())));
    }

    return $user_programs;
  }


  /**
   * @param $program Program
   * @param $user    User
   *
   * @return bool
   */
  private function checkReportedByUser($program, $user)
  {
    $isReportedByUser = false;
    $em = $this->getDoctrine()->getManager();
    $reported_program = $em->getRepository("\App\Entity\ProgramInappropriateReport")
      ->findOneBy(['program' => $program->getId()]);

    if ($reported_program)
    {
      $isReportedByUser = ($user === $reported_program->getReportingUser());
    }

    return $isReportedByUser;
  }
}
