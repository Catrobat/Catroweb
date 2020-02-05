<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Catrobat\Services\RudeWordFilter;
use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\StatisticsService;
use App\Catrobat\Services\TestEnv\FakeStatisticsService;
use App\Catrobat\StatusCode;
use App\Catrobat\Twig\AppExtension;
use App\Entity\LikeNotification;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\RemixManager;
use App\Entity\User;
use App\Entity\UserComment;
use App\Repository\CatroNotificationRepository;
use App\Repository\GameJamRepository;
use App\Utils\ImageUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use ImagickException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\Translation\TranslatorInterface;


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
   * @param Request              $request
   * @param                      $id
   * @param RemixManager         $remix_manager
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
   * @Route("/program/{id}", name="program_depricated")
   * @Route("/details/{id}", name="catrobat_web_detail", methods={"GET"})
   *
   * @param Request                    $request
   * @param                            $id
   * @param ProgramManager             $program_manager
   * @param ScreenshotRepository       $screenshot_repository
   * @param ElapsedTimeStringFormatter $elapsed_time
   * @param RemixManager               $remix_manager
   * @param GameJamRepository          $game_jam_repository
   * @param ExtractedFileRepository    $extractedFileRepository
   *
   * @return Response
   * @throws NonUniqueResultException
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function projectAction(Request $request, $id, ProgramManager $program_manager,
                                ScreenshotRepository $screenshot_repository,
                                ElapsedTimeStringFormatter $elapsed_time,
                                RemixManager $remix_manager, GameJamRepository $game_jam_repository,
                                ExtractedFileRepository $extractedFileRepository)
  {
    /**
     * @var $user             User
     * @var $project          Program
     * @var $reported_program ProgramInappropriateReport
     * @var $like             ProgramLike
     */
    $project = $program_manager->find($id);
    $router = $this->get('router');

    if (!$program_manager->isProjectVisibleForCurrentUser($project))
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $project, $viewed, $program_manager);
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);

    $user = $this->getUser();
    $logged_in = false;

    $active_user_like_types = [];
    $user_name = null;

    if ($user !== null)
    {
      $logged_in = true;
      $user_name = $user->getUsername();
      $likes = $program_manager->findUserLikes($project->getId(), $user->getId());
      foreach ($likes as $like)
      {
        $active_user_like_types[] = $like->getType();
      }
    }
    $active_like_types = $program_manager->findProgramLikeTypes($project->getId());

    $total_like_count = $program_manager->totalLikeCount($project->getId());
    $program_comments = $this->findCommentsById($project);
    $program_details = $this->createProgramDetailsArray($screenshot_repository, $project,
      $active_like_types, $active_user_like_types, $total_like_count, $elapsed_time, $referrer,
      $program_comments, $request, $remix_manager
    );

    $user_programs = $this->findUserPrograms($user, $project);

    $isReportedByUser = $this->checkReportedByUser($project, $user);

    $program_url = $this->generateUrl('program',
      ['id' => $project->getId()], true);
    $share_text = trim($project->getName() . ' on ' . $program_url . ' ' .
      $project->getDescription());

    $jam = $this->extractGameJamConfig($game_jam_repository);

    $max_description_size = $this->getParameter("catrobat.max_description_upload_size");

    $my_program = false;
    if ($user_programs && count($user_programs) > 0)
    {
      $my_program = true;
    }

    $extractedFileRepository->loadProgramExtractedFile($project);

    return $this->render('Program/program.html.twig', [
      'program_details_url_template' => $router->generate('program', ['id' => 0]),
      'program'                      => $project,
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
   * @Route("/project/like/{id}", name="project_like", methods={"GET"})
   *
   * @param Request                     $request
   * @param GuidType                    $id
   * @param ProgramManager              $program_manager
   * @param CatroNotificationRepository $notification_repo
   * @param CatroNotificationService    $notification_service
   *
   * @return JsonResponse|RedirectResponse
   * @throws ORMException
   */
  public function projectLikeAction(Request $request, $id, ProgramManager $program_manager,
                                    CatroNotificationRepository $notification_repo,
                                    CatroNotificationService $notification_service,
                                    TranslatorInterface $translator)
  {
    $csrf_token = $request->query->get('token');
    if (!$this->isCsrfTokenValid('project', $csrf_token))
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::CSRF_FAILURE,
          'message'    => 'Invalid CSRF token.',
        ], Response::HTTP_BAD_REQUEST);
      }
      else
      {
        throw new InvalidCsrfTokenException();
      }
    }

    $type = intval($request->query->get('type'));
    $action = $request->query->get('action');

    if (!ProgramLike::isValidType($type))
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::INVALID_PARAM,
          'message'    => 'Invalid like type given!',
        ], Response::HTTP_BAD_REQUEST);
      }
      else
      {
        throw $this->createAccessDeniedException('Invalid like-type for project given!');
      }
    }

    /** @var Program $project */
    $project = $program_manager->find($id);
    if ($project === null)
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::INVALID_PARAM,
          'message'    => 'Project with given ID does not exist!',
        ], Response::HTTP_NOT_FOUND);
      }
      else
      {
        throw $this->createNotFoundException('Project with given ID does not exist!');
      }
    }

    /** @var User $user */
    $user = $this->getUser();
    if (!$user)
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR], Response::HTTP_UNAUTHORIZED);
      }
      else
      {
        $request->getSession()->set('catroweb_login_redirect', $this->generateUrl(
          'project_like',
          ['id' => $id, 'type' => $type, 'action' => $action, 'token' => $csrf_token],
          UrlGeneratorInterface::ABSOLUTE_URL
        ));

        return $this->redirectToRoute('login');
      }
    }

    try
    {
      $program_manager->changeLike($project, $user, $type, $action);
    } catch (\InvalidArgumentException $exception)
    {
      if ($request->isXmlHttpRequest())
      {
        return JsonResponse::create([
          'statusCode' => StatusCode::INVALID_PARAM,
          'message'    => 'Invalid action given!',
        ], Response::HTTP_BAD_REQUEST);
      }
      else
      {
        throw $this->createAccessDeniedException('Invalid action given!');
      }
    }


    if ($project->getUser() !== $user)
    {
      $existing_notifications = $notification_repo->getLikeNotificationsForProject(
        $project, $project->getUser(), $user
      );

      if ($action === ProgramLike::ACTION_ADD)
      {

        if (count($existing_notifications) === 0)
        {
          $notification = new LikeNotification($project->getUser(), $user, $project);
          $notification_service->addNotification($notification);
        }
      }
      elseif ($action === ProgramLike::ACTION_REMOVE)
      {
        // check if there is no other reaction
        if (!$program_manager->areThereOtherLikeTypes($project, $user, $type))
        {
          foreach ($existing_notifications as $notification)
          {
            $notification_service->removeNotification($notification);
          }
        }
      }
    }

    if (!$request->isXmlHttpRequest())
    {
      return $this->redirectToRoute('program', ['id' => $id]);
    }

    $user_locale = $request->getLocale();
    $total_like_count = $program_manager->totalLikeCount($project->getId());
    $active_like_types = array_map(function ($type_id) {
      return ProgramLike::$TYPE_NAMES[$type_id];
    }, $program_manager->findProgramLikeTypes($project->getId()));

    return new JsonResponse([
      'totalLikeCount'  => [
        'value'       => $total_like_count,
        'stringValue' => AppExtension::humanFriendlyNumber($total_like_count, $translator, $user_locale),
      ],
      'activeLikeTypes' => $active_like_types,
    ]);
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
    return $this->render('Search/search.html.twig', ['q' => $q]);
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

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $user_programs = $user->getPrograms();
    $programs = $user_programs->matching(
      Criteria::create()->where(Criteria::expr()->eq('id', $id))
    );

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
   * @param GuidType            $id
   * @param string              $newDescription
   * @param RudeWordFilter      $rude_word_filter
   * @param ProgramManager      $program_manager
   * @param TranslatorInterface $translator
   *
   * @return Response
   * @throws Exception
   *
   */
  public function editProgramDescription($id, $newDescription, RudeWordFilter $rude_word_filter,
                                         ProgramManager $program_manager, TranslatorInterface $translator)
  {
    /**
     * @var User    $user
     * @var Program $program
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
   * @Route("/editProjectCredits/{id}/{newCredits}", name="edit_program_credits",
   *   options={"expose"=true}, methods={"GET"},)
   *
   * @param GuidType            $id
   * @param string              $newCredits
   * @param RudeWordFilter      $rude_word_filter
   * @param ProgramManager      $program_manager
   * @param TranslatorInterface $translator
   *
   * @return Response
   * @throws Exception
   *
   */
  public function editProgramCredits($id, $newCredits, RudeWordFilter $rude_word_filter,
                                     ProgramManager $program_manager, TranslatorInterface $translator)
  {
    /**
     * @var User    $user
     * @var Program $program
     */

    $max_credits_size = $this->getParameter("catrobat.max_credits_upload_size");

    if (strlen($newCredits) > $max_credits_size)
    {
      return JsonResponse::create(['statusCode' => StatusCode::CREDITS_TO_LONG,
                                   'message'    => $translator
                                     ->trans("programs.tooLongCredits", [], "catroweb")]);
    }

    if ($rude_word_filter->containsRudeWord($newCredits))
    {
      return JsonResponse::create(['statusCode' => StatusCode::RUDE_WORD_IN_CREDITS,
                                   'message'    => $translator
                                     ->trans("programs.rudeWordsInCredits", [], "catroweb")]);
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

    $program->setCredits($newCredits);

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
   * @param Request        $request
   * @param Program        $program
   * @param                $viewed
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
   * @param $active_like_types          int[]
   * @param $active_user_like_types     int[]
   * @param $total_like_count
   * @param $elapsed_time               ElapsedTimeStringFormatter
   * @param $referrer
   * @param $program_comments
   * @param $request                    Request
   * @param $remix_manager              RemixManager
   *
   * @return array
   */
  private function createProgramDetailsArray(ScreenshotRepository $screenshot_repository, $program, $active_like_types,
                                             $active_user_like_types, $total_like_count, $elapsed_time,
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
        'id' => $comment->getUser()->getId()
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
      'screenshotBig'       => $screenshot_repository->getScreenshotWebPath($program->getId()),
      'downloadUrl'         => $url,
      'languageVersion'     => $program->getLanguageVersion(),
      'downloads'           => $program->getDownloads() + $program->getApkDownloads(),
      'views'               => $program->getViews(),
      'filesize'            => sprintf('%.2f', $program->getFilesize() / 1048576),
      'age'                 => $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp()),
      'referrer'            => $referrer,
      'id'                  => $program->getId(),
      'comments'            => $program_comments,
      'commentsLength'      => count($program_comments),
      'commentsAvatars'     => $comments_avatars,
      'remixesLength'       => $remix_manager->remixCount($program->getId()),
      'activeLikeTypes'     => $active_like_types,
      'activeUserLikeTypes' => $active_user_like_types,
      'totalLikeCount'      => $total_like_count,
      'isAdmin'             => $this->isGranted("ROLE_ADMIN"),
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
        ['program' => $program->getId()], ['id' => 'DESC']);

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

  /**
   * @Route("/project/{id}/uploadThumbnail", name="upload_project_thumbnail", methods={"POST"})
   *
   * @param Request              $request
   * @param ProgramManager       $project_manager
   * @param ScreenshotRepository $screenshot_repository
   * @param                      $id
   *
   * @return JsonResponse|RedirectResponse
   * @throws ImagickException
   */
  public function uploadAvatarAction(Request $request, ProgramManager $project_manager,
                                     ScreenshotRepository $screenshot_repository, $id)
  {
    /**
     * @var $user    User
     * @var $project Program
     */
    $user = $this->getUser();
    $project = $project_manager->find($id);

    if (!$user || $project->getUser() !== $user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $image = $request->request->get('image');

    try
    {
      $image = ImageUtils::checkAndResizeBase64Image($image, null);
    } catch (Exception $e)
    {
      return JsonResponse::create(['statusCode' => $e->getMessage()]);
    }

    $screenshot_repository->updateProgramAssets($image, $id);

    return JsonResponse::create([
      'statusCode'   => StatusCode::OK,
      'image_base64' => null,
    ]);
  }
}
