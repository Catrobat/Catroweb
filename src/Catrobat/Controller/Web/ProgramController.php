<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\CatroNotification;
use App\Entity\LikeNotification;
use App\Entity\UserComment;
use App\Catrobat\Services\CatroNotificationService;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\StatusCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Error\Error;


/**
 * Class ProgramController
 * @package App\Catrobat\Controller\Web
 */
class ProgramController extends Controller
{

  /**
   * @Route("/program/remixgraph/{id}", name="program_remix_graph",
   *   requirements={"id":"\d+"}, methods={"GET"})
   *
   * @param Request $request
   * @param integer $id
   *
   * @return JsonResponse
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Geocoder\Exception\Exception
   */
  public function programRemixGraphAction(Request $request, $id)
  {
    $remix_graph_data = $this->get('remixmanager')->getFullRemixGraph($id);
    $screenshot_repository = $this->get('screenshotrepository');

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

    $statistics = $this->get('statistics');
    $locale = strtolower($request->getLocale());
    $referrer = $request->headers->get('referer');
    $statistics->createClickStatistics($request, 'show_remix_graph', 0, $id, null,
      null, $referrer, $locale, false, false);

    return new JsonResponse([
      'id'                        => $id,
      'remixGraph'                => $remix_graph_data,
      'catrobatProgramThumbnails' => $catrobat_program_thumbnails,
    ]);
  }


  /**
   * @Route("/program/{id}", name="program", requirements={"id":"\d+"})
   * @Route("/details/{id}", name="catrobat_web_detail", requirements={"id":"\d+"},
   *   methods={"GET"})
   *
   * @param Request $request
   * @param integer $id
   *
   * @return JsonResponse
   * @throws Error
   * @throws \Doctrine\ORM\NonUniqueResultException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function programAction(Request $request, $id)
  {
    /**
     * @var $user             User
     * @var $program          Program
     * @var $reported_program ProgramInappropriateReport
     * @var $like             ProgramLike
     */
    $program_manager = $this->get('programmanager');
    $program = $program_manager->find($id);
    $featured_repository = $this->get('featuredrepository');
    $screenshot_repository = $this->get('screenshotrepository');
    $router = $this->get('router');
    $elapsed_time = $this->get('elapsedtime');

    if (!$program || !$program->isVisible())
    {
      if (!$featured_repository->isFeatured($program))
      {
        throw $this->createNotFoundException('Unable to find Project entity.');
      }
    }

    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $program, $viewed);
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
      $program_comments, $request);

    $user_programs = $this->findUserPrograms($user, $program);

    $isReportedByUser = $this->checkReportedByUser($program, $user);

    $program_url = $this->generateUrl('program',
      ['id' => $program->getId()], true);
    $share_text = trim($program->getName() . ' on ' . $program_url . ' ' .
      $program->getDescription());

    $jam = $this->extractGameJamConfig();

    $max_description_size = $this->container->get('kernel')->getContainer()
      ->getParameter("catrobat.max_description_upload_size");


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
   * @Route("/program/like/{id}", name="program_like", requirements={"id":"\d+"}, methods={"GET"})
   *
   * @param Request $request
   * @param integer $id
   *
   * @throws \Exception
   *
   * @return JsonResponse|RedirectResponse
   */
  public function programLikeAction(Request $request, $id)
  {
    /**
     * @var ProgramManager           $program_manager
     * @var User                     $user
     * @var Program                  $program
     * @var CatroNotification        $notification
     * @var CatroNotificationService $notification_service
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
    $program_manager = $this->get('programmanager');
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

      $notification_repo = $this->get("catro_notification_repository");
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

      $notification_service = $this->get("catro_notification_service");
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
   * @throws \Exception
   *
   * @return JsonResponse|RedirectResponse
   */
  public function searchAction($q)
  {
    return $this->get('templating')->renderResponse('Search/search.html.twig', ['q' => $q]);
  }


  /**
   * @Route("/profileDeleteProgram/{id}", name="profile_delete_program", requirements={"id":"\d+"},
   *    defaults={"id" = 0}, methods={"GET"})
   *
   * @param integer $id
   *
   * @throws \Exception
   *
   * @return JsonResponse|RedirectResponse
   */
  public function deleteProgramAction($id)
  {
    /**
     * @var $user          \App\Entity\User
     * @var $program       \App\Entity\Program
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
   * @Route("/profileToggleProgramVisibility/{id}", name="profile_toggle_program_visibility",
   *   requirements={"id":"\d+"}, defaults={"id" = 0}, methods={"GET"})
   *
   * @param integer $id
   *
   * @throws \Exception
   *
   * @return Response
   */
  public function toggleProgramVisibilityAction($id)
  {
    /**
     * @var $user User
     * @var $program Program
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
   * @Route("/editProgramDescription/{id}/{newDescription}", name="edit_program_description",
   *   options={"expose"=true}, requirements={"id":"\d+"}, methods={"GET"})
   *
   * @param integer $id
   * @param string  $newDescription
   *
   * @throws \Exception
   *
   * @return Response
   */
  public function editProgramDescription($id, $newDescription)
  {
    /**
     * @var                $user    \App\Entity\User
     * @var                $program \App\Entity\Program
     * @var ProgramManager $program_manager
     */

    $rude_word_filter = $this->get('rudewordfilter');
    $max_description_size = $this->container->get('kernel')->getContainer()
      ->getParameter("catrobat.max_description_upload_size");
    $translator = $this->get('translator');

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

    $program_manager = $this->get('programmanager');
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
   * @return array
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  private function extractGameJamConfig()
  {
    $jam = null;
    $gamejam = $this->get('gamejamrepository')->getCurrentGameJam();

    if ($gamejam)
    {
      $gamejam_flavor = $gamejam->getFlavor();
      if ($gamejam_flavor !== null)
      {
        $config = $this->container->getParameter('gamejam');
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
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  private function checkAndAddViewed(Request $request, $program, $viewed)
  {
    if (!in_array($program->getId(), $viewed))
    {
      $this->get('programmanager')->increaseViews($program);
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
   *
   * @return array
   */
  private function createProgramDetailsArray($screenshot_repository, $program, $like_type,
                                             $like_type_count, $total_like_count, $elapsed_time,
                                             $referrer, $program_comments, $request)
  {
    $rec_by_page_id = intval($request->query
      ->get('rec_by_page_id', RecommendedPageId::INVALID_PAGE));
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
        $url = $this->generateUrl('download', ['id'    => $program->getId(),
                                               'fname' => $program->getName()]);
      }
    }

    $comments_avatars = [];
    foreach ($program_comments as $comment)
    {
      /**
       * @var   $em      \Doctrine\ORM\EntityManager
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
      'remixesLength'   => $this->get('remixmanager')->remixCount($program->getId()),
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
   * @return array|\App\Entity\UserComment[]
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
