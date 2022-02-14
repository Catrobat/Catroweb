<?php

namespace App\Application\Controller\Project;

use App\Application\Twig\TwigExtension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProgramFileRepository;
use App\Project\Event\CheckScratchProgramEvent;
use App\Project\ProgramManager;
use App\Storage\ScreenshotRepository;
use App\Translation\TranslationDelegate;
use App\User\Notification\NotificationManager;
use App\Utils\ElapsedTimeStringFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Exception;
use ImagickException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProgramController extends AbstractController
{
  private ScreenshotRepository $screenshot_repository;
  private ProgramManager $program_manager;
  private ElapsedTimeStringFormatter $elapsed_time;
  private ExtractedFileRepository $extracted_file_repository;
  private NotificationRepository $notification_repo;
  private NotificationManager $notification_service;
  private TranslatorInterface $translator;
  private ParameterBagInterface $parameter_bag;
  private EventDispatcherInterface $event_dispatcher;
  private ProgramFileRepository $file_repository;
  private TranslationDelegate $translation_delegate;

  public function __construct(ScreenshotRepository $screenshot_repository,
                              ProgramManager $program_manager,
                              ElapsedTimeStringFormatter $elapsed_time,
                              ExtractedFileRepository $extracted_file_repository,
                              NotificationRepository $notification_repo,
                              NotificationManager $notification_service,
                              TranslatorInterface $translator,
                              ParameterBagInterface $parameter_bag,
                              EventDispatcherInterface $event_dispatcher,
                              ProgramFileRepository $file_repository,
                              TranslationDelegate $translation_delegate)
  {
    $this->screenshot_repository = $screenshot_repository;
    $this->program_manager = $program_manager;
    $this->elapsed_time = $elapsed_time;
    $this->extracted_file_repository = $extracted_file_repository;
    $this->notification_repo = $notification_repo;
    $this->notification_service = $notification_service;
    $this->translator = $translator;
    $this->parameter_bag = $parameter_bag;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->translation_delegate = $translation_delegate;
  }

  /**
   * @Route("/project/{id}", name="program", defaults={"id": "0"})
   *
   * Legacy routes:
   * @Route("/program/{id}", name="program_depricated")
   *
   * Legacy routes
   * @Route("/details/{id}", name="catrobat_web_detail", methods={"GET"})
   */
  public function projectAction(Request $request, string $id): Response
  {
    $project = $this->program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      $this->addFlash('snackbar', $this->translator->trans('snackbar.project_not_found', [], 'catroweb'));

      return $this->redirect($this->generateUrl('index'));
    }

    if ($project->isScratchProgram()) {
      $this->event_dispatcher->dispatch(new CheckScratchProgramEvent($project->getScratchId()));
    }

    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $project, $viewed);
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);

    /** @var User|null $user */
    $user = $this->getUser();
    $logged_in = null !== $user;
    $my_program = $logged_in && $project->getUser() === $user;

    $active_user_like_types = [];
    if ($logged_in) {
      $likes = $this->program_manager->findUserLikes($project->getId(), $user->getId());
      foreach ($likes as $like) {
        $active_user_like_types[] = $like->getType();
      }
    }
    $active_like_types = $this->program_manager->findProgramLikeTypes($project->getId());

    $total_like_count = $this->program_manager->totalLikeCount($project->getId());
    $program_comments = $this->findCommentsById($project);
    $program_details = $this->createProgramDetailsArray(
      $project, $active_like_types, $active_user_like_types, $total_like_count,
      $referrer, $program_comments
    );

    return $this->render('Program/program.html.twig', [
      'program' => $project,
      'program_details' => $program_details,
      'my_program' => $my_program,
      'logged_in' => $logged_in,
      'max_name_size' => $this->getParameter('catrobat.max_name_upload_size'),
      'max_description_size' => $this->getParameter('catrobat.max_description_upload_size'),
      'extracted_path' => $this->parameter_bag->get('catrobat.file.extract.path'),
    ]);
  }

  /**
   * @Route("/project/like/{id}", name="project_like", methods={"GET"})
   *
   * @throws ORMException
   */
  public function projectLikeAction(Request $request, string $id): Response
  {
    $csrf_token = $request->query->get('token');
    if (!$this->isCsrfTokenValid('project', $csrf_token)) {
      if ($request->isXmlHttpRequest()) {
        return JsonResponse::create([
          'statusCode' => 706,
          'message' => 'Invalid CSRF token.',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw new InvalidCsrfTokenException();
    }

    $type = intval($request->query->get('type'));
    $action = $request->query->get('action');

    if (!ProgramLike::isValidType($type)) {
      if ($request->isXmlHttpRequest()) {
        return JsonResponse::create([
          'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
          'message' => 'Invalid like type given!',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw $this->createAccessDeniedException('Invalid like-type for project given!');
    }

    $project = $this->program_manager->find($id);
    if (null === $project) {
      if ($request->isXmlHttpRequest()) {
        return JsonResponse::create([
          'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
          'message' => 'Project with given ID does not exist!',
        ], Response::HTTP_NOT_FOUND);
      }

      throw $this->createNotFoundException('Project with given ID does not exist!');
    }

    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      if ($request->isXmlHttpRequest()) {
        return JsonResponse::create(['statusCode' => 601], Response::HTTP_UNAUTHORIZED);
      }

      $request->getSession()->set('catroweb_login_redirect', $this->generateUrl(
          'project_like',
          ['id' => $id, 'type' => $type, 'action' => $action, 'token' => $csrf_token],
          UrlGeneratorInterface::ABSOLUTE_URL
        ));

      return $this->redirectToRoute('login');
    }

    try {
      $this->program_manager->changeLike($project, $user, $type, $action);
    } catch (InvalidArgumentException $exception) {
      if ($request->isXmlHttpRequest()) {
        return JsonResponse::create([
          'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
          'message' => 'Invalid action given!',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw $this->createAccessDeniedException('Invalid action given!');
    }

    if ($project->getUser() !== $user) {
      $existing_notifications = $this->notification_repo->getLikeNotificationsForProject(
        $project, $project->getUser(), $user
      );

      if (ProgramLike::ACTION_ADD === $action) {
        if (0 === count($existing_notifications)) {
          $notification = new LikeNotification($project->getUser(), $user, $project);
          $this->notification_service->addNotification($notification);
        }
      } elseif (ProgramLike::ACTION_REMOVE === $action) {
        // check if there is no other reaction
        if (!$this->program_manager->areThereOtherLikeTypes($project, $user, $type)) {
          foreach ($existing_notifications as $notification) {
            $this->notification_service->removeNotification($notification);
          }
        }
      }
    }

    if (!$request->isXmlHttpRequest()) {
      return $this->redirectToRoute('program', ['id' => $id]);
    }

    $user_locale = $request->getLocale();
    $total_like_count = $this->program_manager->totalLikeCount($project->getId());
    $active_like_types = array_map(function ($type_id) {
      return ProgramLike::$TYPE_NAMES[$type_id];
    }, $this->program_manager->findProgramLikeTypes($project->getId()));

    return new JsonResponse([
      'totalLikeCount' => [
        'value' => $total_like_count,
        'stringValue' => TwigExtension::humanFriendlyNumber($total_like_count, $this->translator, $user_locale),
      ],
      'activeLikeTypes' => $active_like_types,
    ]);
  }

  /**
   * @Route("/search/{q}", name="search", requirements={"q": ".+"}, methods={"GET"})
   * @Route("/search/", name="empty_search", defaults={"q": null}, methods={"GET"})
   */
  public function searchAction(string $q): Response
  {
    return $this->render('Search/search.html.twig', ['q' => $q]);
  }

  /**
   * @Route("/userDeleteProject/{id}", name="profile_delete_program", methods={"GET"})
   *
   * @throws Exception
   */
  public function deleteProgramAction(string $id = ''): Response
  {
    if ('' === $id) {
      return $this->redirectToRoute('profile');
    }

    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    /** @var ArrayCollection $user_programs */
    $user_programs = $user->getPrograms();
    $programs = $user_programs->matching(Criteria::create()
      ->where(Criteria::expr()->eq('id', $id)));

    if ($programs->isEmpty()) {
      return $this->redirectToRoute('profile');
    }

    /** @var Program $program */
    $program = $programs[0];
    $program->setVisible(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    return $this->redirectToRoute('profile');
  }

  /**
   * @Route("/userToggleProjectVisibility/{id}", name="profile_toggle_program_visibility",
   * defaults={"id": 0}, methods={"GET"})
   *
   * @throws Exception
   */
  public function toggleProgramVisibilityAction(string $id): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    /** @var ArrayCollection $user_programs */
    $user_programs = $user->getPrograms();

    $programs = $user_programs->matching(
      Criteria::create()->where(Criteria::expr()->eq('id', $id))
    );

    if ($programs->isEmpty()) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    /** @var Program $program */
    $program = $programs[0];
    $program->setPrivate(!$program->getPrivate());

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    return new Response('true');
  }

  /**
   * @Route("/editProjectName/{id}", name="edit_program_name", methods={"PUT"})
   *
   * @throws Exception
   */
  public function editProgramName(Request $request, string $id): Response
  {
    $max_name_size = (int) $this->getParameter('catrobat.max_name_upload_size');
    $value = $request->request->get('value');

    if (strlen($value) > $max_name_size) {
      return Response::create(
        $this->translator->trans('programs.tooLongName', [], 'catroweb'),
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    $program = $this->program_manager->find($id);
    if (!$program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    if ($program->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your program!');
    }

    $program->setName($value);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    $extracted_file = $this->extracted_file_repository->loadProgramExtractedFile($program);
    if ($extracted_file) {
      $extracted_file->setName($value);
      $this->extracted_file_repository->saveProgramExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($program->getId());
    }

    return Response::create();
  }

  /**
   * @Route("/editProjectDescription/{id}", name="edit_program_description", methods={"PUT"})
   *
   * @throws Exception
   */
  public function editProgramDescription(Request $request, string $id): Response
  {
    $max_description_size = (int) $this->getParameter('catrobat.max_description_upload_size');
    $value = $request->request->get('value');

    if (strlen($value) > $max_description_size) {
      return JsonResponse::create(['statusCode' => 527,
        'message' => $this->translator
          ->trans('programs.tooLongDescription', [], 'catroweb'), ]);
    }

    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    $program = $this->program_manager->find($id);
    if (!$program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    if ($program->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your program!');
    }

    $program->setDescription($value);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    $extracted_file = $this->extracted_file_repository->loadProgramExtractedFile($program);
    if ($extracted_file) {
      $extracted_file->setDescription($value);
      $this->extracted_file_repository->saveProgramExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($program->getId());
    }

    return JsonResponse::create(['statusCode' => Response::HTTP_OK]);
  }

  /**
   * @Route("/editProjectCredits/{id}", name="edit_program_credits", methods={"PUT"})
   *
   * @throws Exception
   */
  public function editProgramCredits(Request $request, string $id): Response
  {
    $max_credits_size = (int) $this->getParameter('catrobat.max_notes_and_credits_upload_size');
    $value = $request->request->get('value');

    if (strlen($value) > $max_credits_size) {
      return JsonResponse::create(['statusCode' => 707,
        'message' => $this->translator
          ->trans('programs.tooLongCredits', [], 'catroweb'), ]);
    }

    $user = $this->getUser();
    if (null === $user) {
      return JsonResponse::create(null, Response::HTTP_UNAUTHORIZED);
    }

    $program = $this->program_manager->find($id);
    if (null === $program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    if ($program->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your program!');
    }

    $program->setCredits($value);

    $em = $this->getDoctrine()->getManager();
    $em->persist($program);
    $em->flush();

    $extracted_file = $this->extracted_file_repository->loadProgramExtractedFile($program);
    if ($extracted_file) {
      $extracted_file->setNotesAndCredits($value);
      $this->extracted_file_repository->saveProgramExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($program->getId());
    }

    return JsonResponse::create(['statusCode' => Response::HTTP_OK]);
  }

  /**
   * @Route("/project/{id}/image", name="upload_project_thumbnail", methods={"POST"})
   *
   * @throws ImagickException
   */
  public function uploadProjectImage(Request $request, string $id): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    /** @var Program|null $project */
    $project = $this->program_manager->find($id);

    if (null === $project) {
      throw new NotFoundHttpException();
    }

    if (null === $user || $project->getUser() !== $user) {
      return $this->redirectToRoute('login');
    }

    $image = $request->request->get('image');

    $this->screenshot_repository->updateProgramAssets($image, $id);

    return JsonResponse::create();
  }

  /**
   * @Route("/translate/project/{id}", name="translate_project", methods={"GET"})
   *
   * @throws NoResultException
   */
  public function translateProjectAction(Request $request, string $id): Response
  {
    if (!$request->query->has('target_language')) {
      return new Response('Target language is required', Response::HTTP_BAD_REQUEST);
    }

    $project = $this->program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return new Response('No project found for this id', Response::HTTP_NOT_FOUND);
    }

    $source_language = $request->query->get('source_language');
    $target_language = $request->query->get('target_language');

    if ($source_language === $target_language) {
      return new Response('Source and target languages are the same', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $response = new JsonResponse();
    $response->setEtag(md5($project->getName().$project->getDescription().$project->getCredits()).$target_language);
    $response->setPublic();

    if ($response->isNotModified($request)) {
      return $response;
    }

    $translation_result = $this->translation_delegate->translateProject($project, $source_language, $target_language);

    if (null === $translation_result) {
      return new Response('Translation unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
    }

    return $response->setData([
      'id' => $project->getId(),
      'source_language' => $source_language ?? $translation_result[0]->detected_source_language,
      'target_language' => $target_language,
      'translated_title' => $translation_result[0]->translation,
      'translated_description' => $translation_result[1] ? $translation_result[1]->translation : null,
      'translated_credit' => $translation_result[2] ? $translation_result[2]->translation : null,
      'provider' => $translation_result[0]->provider,
    ]);
  }

  /**
   * @Route("/translate/custom/project/{id}", name="project_custom_translation", methods={"PUT", "GET", "DELETE"})
   */
  public function projectCustomTranslationAction(Request $request, string $id): Response
  {
    switch ($request->getMethod()) {
      case 'PUT':
        return $this->projectCustomTranslationPutAction($request, $id);
      case 'GET':
        return $this->projectCustomTranslationGetAction($request, $id);
      case 'DELETE':
        return $this->projectCustomTranslationDeleteAction($request, $id);
      default:
        return Response::create(null, Response::HTTP_BAD_REQUEST);
    }
  }

  /**
   * @Route("/translate/custom/project/{id}/list", name="project_custom_translation_language_list", methods={"GET"})
   */
  public function projectCustomTranslationLanguageListAction(Request $request, string $id,
                                                             ProjectCustomTranslationRepository $repository): Response
  {
    $project = $this->program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return Response::create(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')) {
      return JsonResponse::create($repository->listDefinedLanguages($project));
    }

    $field = $request->query->get('field');

    switch ($field) {
      case 'name':
        return JsonResponse::create($repository->listDefinedLanguagesForName($project));
      case 'description':
        return JsonResponse::create($repository->listDefinedLanguagesForDescription($project));
      case 'credit':
        return JsonResponse::create($repository->listDefinedLanguagesForCredit($project));
      default:
        return Response::create(null, Response::HTTP_BAD_REQUEST);
    }
  }

  private function checkAndAddViewed(Request $request, Program $program, array $viewed): void
  {
    if (!in_array($program->getId(), $viewed, true)) {
      $this->program_manager->increaseViews($program);
      $viewed[] = $program->getId();
      $request->getSession()->set('viewed', $viewed);
    }
  }

  private function createProgramDetailsArray(Program $program,
                                             array $active_like_types,
                                             array $active_user_like_types,
                                             int $total_like_count,
                                             ?string $referrer,
                                             array $program_comments): array
  {
    $url = $this->generateUrl('download', ['id' => $program->getId(), 'fname' => $program->getName()]);

    $comments_avatars = [];
    /** @var UserComment $comment */
    foreach ($program_comments as $comment) {
      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository(User::class)->findOneBy([
        'id' => $comment->getUser()->getId(),
      ]);
      if (null !== $user) {
        $avatar = $user->getAvatar();
        if ($avatar) {
          $comments_avatars[$comment->getId()] = $avatar;
        }
      }
    }

    return [
      'screenshotBig' => $this->screenshot_repository->getScreenshotWebPath($program->getId()),
      'downloadUrl' => $url,
      'languageVersion' => $program->getLanguageVersion(),
      'downloads' => $program->getDownloads() + $program->getApkDownloads(),
      'views' => $program->getViews(),
      'filesize' => sprintf('%.2f', $program->getFilesize() / 1048576),
      'age' => $this->elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp()),
      'referrer' => $referrer,
      'id' => $program->getId(),
      'comments' => $program_comments,
      'commentsLength' => count($program_comments),
      'commentsAvatars' => $comments_avatars,
      'activeLikeTypes' => $active_like_types,
      'activeUserLikeTypes' => $active_user_like_types,
      'totalLikeCount' => $total_like_count,
      'isAdmin' => $this->isGranted('ROLE_ADMIN'),
    ];
  }

  /**
   * @return array|UserComment[]
   */
  private function findCommentsById(Program $program): array
  {
    return $this->getDoctrine()
      ->getRepository(UserComment::class)
      ->findBy(
        ['program' => $program->getId()],
        ['id' => 'DESC']
      )
      ;
  }

  private function findUserPrograms(?User $user, Program $program): ?Collection
  {
    $user_programs = null;
    if (null !== $user) {
      /** @var ArrayCollection $programs */
      $programs = $user->getPrograms();
      $user_programs = $programs->matching(Criteria::create()
        ->where(Criteria::expr()->eq('id', $program->getId())));
    }

    return $user_programs;
  }

  private function checkReportedByUser(Program $program, ?User $user): bool
  {
    $isReportedByUser = false;
    if (null === $user) {
      return $isReportedByUser;
    }
    $em = $this->getDoctrine()->getManager();
    $reported_program = $em->getRepository(ProgramInappropriateReport::class)
      ->findOneBy(['program' => $program->getId()])
    ;

    if ($reported_program) {
      $isReportedByUser = ($user === $reported_program->getReportingUser());
    }

    return $isReportedByUser;
  }

  private function projectCustomTranslationDeleteAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return Response::create(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->program_manager->find($id);
    if (null === $project || $project->getUser() !== $user) {
      return Response::create(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return Response::create(null, Response::HTTP_BAD_REQUEST);
    }

    $field = $request->query->get('field');
    $language = $request->query->get('language');

    try {
      switch ($field) {
        case 'name':
          $result = $this->translation_delegate->deleteProjectNameCustomTranslation($project, $language);
          break;
        case 'description':
          $result = $this->translation_delegate->deleteProjectDescriptionCustomTranslation($project, $language);
          break;
        case 'credit':
          $result = $this->translation_delegate->deleteProjectCreditCustomTranslation($project, $language);
          break;
        default:
          return Response::create(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (InvalidArgumentException $exception) {
      return Response::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return Response::create(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }

  private function projectCustomTranslationGetAction(Request $request, string $id): Response
  {
    $project = $this->program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return Response::create(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return Response::create(null, Response::HTTP_BAD_REQUEST);
    }

    $field = $request->query->get('field');
    $language = $request->query->get('language');

    try {
      switch ($field) {
        case 'name':
          $result = $this->translation_delegate->getProjectNameCustomTranslation($project, $language);
          break;
        case 'description':
          $result = $this->translation_delegate->getProjectDescriptionCustomTranslation($project, $language);
          break;
        case 'credit':
          $result = $this->translation_delegate->getProjectCreditCustomTranslation($project, $language);
          break;
        default:
          return Response::create(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (InvalidArgumentException $exception) {
      return Response::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return Response::create($result, null == $result ? Response::HTTP_NOT_FOUND : Response::HTTP_OK);
  }

  private function projectCustomTranslationPutAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return Response::create(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->program_manager->find($id);
    if (null === $project || $project->getUser() !== $user) {
      return Response::create(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')
      || !$request->query->has('text')) {
      return Response::create(null, Response::HTTP_BAD_REQUEST);
    }

    $field = $request->query->get('field');
    $language = $request->query->get('language');
    $text = $request->query->get('text');

    if ('' === trim($text)) {
      return Response::create(null, Response::HTTP_BAD_REQUEST);
    }

    try {
      switch ($field) {
        case 'name':
          $result = $this->translation_delegate->addProjectNameCustomTranslation($project, $language, $text);
          break;
        case 'description':
          $result = $this->translation_delegate->addProjectDescriptionCustomTranslation($project, $language, $text);
          break;
        case 'credit':
          $result = $this->translation_delegate->addProjectCreditCustomTranslation($project, $language, $text);
          break;
        default:
          return Response::create(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (InvalidArgumentException $exception) {
      return Response::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return Response::create(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }
}
