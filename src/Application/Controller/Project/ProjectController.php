<?php

namespace App\Application\Controller\Project;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Application\Twig\TwigExtension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\Event\CheckScratchProjectEvent;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use App\Translation\TranslationDelegate;
use App\User\Notification\NotificationManager;
use App\Utils\ElapsedTimeStringFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectController extends AbstractController
{
  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly ProjectManager $project_manager,
    private readonly ElapsedTimeStringFormatter $elapsed_time,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly NotificationRepository $notification_repo,
    private readonly NotificationManager $notification_service,
    private readonly TranslatorInterface $translator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly EventDispatcherInterface $event_dispatcher,
    private readonly ProjectFileRepository $file_repository,
    private readonly TranslationDelegate $translation_delegate,
    private readonly EntityManagerInterface $entity_manager,
    private readonly UserCommentRepository $comment_repository
  ) {
  }

  /**
   * Legacy routes:.
   *
   * Legacy routesdump('Page loaded');
   */
  #[Route(path: '/project/{id}', name: 'program', defaults: ['id' => 0])]
  #[Route(path: '/program/{id}', name: 'program_deprecated')]
  #[Route(path: '/details/{id}', name: 'catrobat_web_detail', methods: ['GET'])]
  #[Route(path: '/steal/{id}', name: 'steal_project', methods: ['POST'])]
  public function projectAction(Request $request, string $id): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      $this->addFlash('snackbar', $this->translator->trans('snackbar.project_not_found', [], 'catroweb'));

      return $this->redirectToRoute('index');
    }
    if ($project->isScratchProgram()) {
      $this->event_dispatcher->dispatch(new CheckScratchProjectEvent($project->getScratchId()));
    }
    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $project, $viewed);
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);

    /** @var User|null $user */
    $user = $this->getUser();
    $logged_in = null !== $user;
    $my_project = $logged_in && $project->getUser() === $user;
    $active_user_like_types = [];
    if ($logged_in) {
      $likes = $this->project_manager->findUserLikes($project->getId(), $user->getId());
      foreach ($likes as $like) {
        $active_user_like_types[] = $like->getType();
      }
    }
    $active_like_types = $this->project_manager->findProjectLikeTypes($project->getId());
    $total_like_count = $this->project_manager->totalLikeCount($project->getId());
    $login_redirect = $this->generateUrl('login', [], UrlGeneratorInterface::ABSOLUTE_URL);

    $project_comment_list = $this->comment_repository->getProjectCommentOverviewListData($project);
    $project_details = $this->createProjectDetailsArray(
      $project, $active_like_types, $active_user_like_types, $total_like_count,
      $referrer, $project_comment_list
    );
        $can_steal = $logged_in && !$my_project;

      if ($can_steal && $request->request->get('steal_project')) {

          $project->setUser($user);
          $this->entity_manager->persist($project);
          $this->entity_manager->flush();

          return $this->redirectToRoute('program', ['id' => $id]);
      }


    return $this->render('Project/project.html.twig', [
      'project' => $project,
      'login_redirect' => $login_redirect,
      'project_details' => $project_details,
      'my_project' => $my_project,
      'logged_in' => $logged_in,
      'max_name_size' => ProjectsRequestValidator::MAX_NAME_LENGTH,
      'max_description_size' => ProjectsRequestValidator::MAX_DESCRIPTION_LENGTH,
      'extracted_path' => $this->parameter_bag->get('catrobat.file.extract.path'),
    ]);
  }

  /**
   * @throws NoResultException
   */
  #[Route(path: '/project/like/{id}', name: 'project_like', methods: ['GET'])]
  public function projectLikeAction(Request $request, string $id): Response
  {
    $type = $request->query->getInt('type');
    $action = (string) $request->query->get('action');
    if (!ProgramLike::isValidType($type)) {
      if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
          'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
          'message' => 'Invalid like type given!',
        ], Response::HTTP_BAD_REQUEST);
      }

      throw $this->createAccessDeniedException('Invalid like-type for project given!');
    }
    $project = $this->project_manager->find($id);
    if (null === $project) {
      if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
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
        return new JsonResponse(['statusCode' => 401], Response::HTTP_UNAUTHORIZED);
      }

      $request->getSession()->set('catroweb_login_redirect', $this->generateUrl(
        'project_like',
        ['id' => $id, 'type' => $type, 'action' => $action],
        UrlGeneratorInterface::ABSOLUTE_URL
      ));

      return $this->redirectToRoute('login');
    }
    try {
      $this->project_manager->changeLike($project, $user, $type, $action);
    } catch (\InvalidArgumentException) {
      if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
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
        if (!$this->project_manager->areThereOtherLikeTypes($project, $user, $type)) {
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
    $total_like_count = $this->project_manager->totalLikeCount($project->getId());
    $active_like_types = array_map(fn ($type_id) => ProgramLike::$TYPE_NAMES[$type_id], $this->project_manager->findProjectLikeTypes($project->getId()));

    return new JsonResponse([
      'totalLikeCount' => [
        'value' => $total_like_count,
        'stringValue' => TwigExtension::humanFriendlyNumber($total_like_count, $this->translator, $user_locale),
      ],
      'activeLikeTypes' => $active_like_types,
    ]);
  }

  #[Route(path: '/search/{q}', name: 'search', requirements: ['q' => '.+'], methods: ['GET'])]
  #[Route(path: '/search/', name: 'empty_search', defaults: ['q' => null], methods: ['GET'])]
  public function searchAction(?string $q = null): Response
  {
    return $this->render('Search/search.html.twig', ['q' => $q]);
  }

  #[Route(path: '/search_old/{q}', name: 'search_old', requirements: ['q' => '.+'], methods: ['GET'])]
  #[Route(path: '/search_old/', name: 'empty_search_old', defaults: ['q' => null], methods: ['GET'])]
  public function searchActionOld(string $q): Response
  {
    return $this->render('Search/searchOld.html.twig', ['q' => $q]);
  }

  /**
   * @deprecated Use new API
   * @see \App\Api\ProjectsApi::projectIdPut() Use this method instead.
   *
   * @throws \Exception
   */
  #[Route(path: '/editProjectName/{id}', name: 'edit_project_name', methods: ['PUT'])]
  public function editProjectName(Request $request, string $id): Response
  {
    $value = (string) $request->request->get('value');
    if (strlen($value) > ProjectsRequestValidator::MAX_NAME_LENGTH) {
      return new Response(
        $this->translator->trans('project.tooLongName', [], 'catroweb'),
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }
    $project = $this->project_manager->find($id);
    if (!$project) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    if ($project->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your project!');
    }
    $project->setName($value);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $extracted_file = $this->extracted_file_repository->loadProjectExtractedFile($project);
    if ($extracted_file) {
      $extracted_file->setName($value);
      $this->extracted_file_repository->saveProjectExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($project->getId());
    }

    return new Response();
  }

  /**
   * @deprecated Use new API
   * @see \App\Api\ProjectsApi::projectIdPut() Use this method instead.
   *
   * @throws \Exception
   */
  #[Route(path: '/editProjectDescription/{id}', name: 'edit_project_description', methods: ['PUT'])]
  public function editProjectDescription(Request $request, string $id): Response
  {
    $value = (string) $request->request->get('value');
    if (strlen($value) > ProjectsRequestValidator::MAX_DESCRIPTION_LENGTH) {
      return new JsonResponse(['statusCode' => 527,
        'message' => $this->translator
          ->trans('project.tooLongDescription', [], 'catroweb'), ]);
    }
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }
    $project = $this->project_manager->find($id);
    if (!$project) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    if ($project->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your project!');
    }
    $project->setDescription($value);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $extracted_file = $this->extracted_file_repository->loadProjectExtractedFile($project);
    if ($extracted_file) {
      $extracted_file->setDescription($value);
      $this->extracted_file_repository->saveProjectExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($project->getId());
    }

    return new JsonResponse(['statusCode' => Response::HTTP_OK]);
  }

  /**
   * @deprecated Use new API
   * @see \App\Api\ProjectsApi::projectIdPut() Use this method instead.
   *
   * @throws \Exception
   */
  #[Route(path: '/editProjectCredits/{id}', name: 'edit_project_credits', methods: ['PUT'])]
  public function editProjectCredits(Request $request, string $id): Response
  {
    $value = (string) $request->request->get('value');
    if (strlen($value) > ProjectsRequestValidator::MAX_CREDITS_LENGTH) {
      return new JsonResponse(['statusCode' => 707,
        'message' => $this->translator
          ->trans('project.tooLongCredits', [], 'catroweb'), ]);
    }
    $user = $this->getUser();
    if (null === $user) {
      return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }
    $project = $this->project_manager->find($id);
    if (null === $project) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    if ($project->getUser() !== $user) {
      throw $this->createAccessDeniedException('Not your project!');
    }
    $project->setCredits($value);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $extracted_file = $this->extracted_file_repository->loadProjectExtractedFile($project);
    if ($extracted_file) {
      $extracted_file->setNotesAndCredits($value);
      $this->extracted_file_repository->saveProjectExtractedFile($extracted_file);
      $this->file_repository->deleteProjectZipFileIfExists($project->getId());
    }

    return new JsonResponse(['statusCode' => Response::HTTP_OK]);
  }

  /**
   * @throws NoResultException
   */
  #[Route(path: '/translate/project/{id}', name: 'translate_project', methods: ['GET'])]
  public function translateProjectAction(Request $request, string $id): Response
  {
    if (!$request->query->has('target_language')) {
      return new Response('Target language is required', Response::HTTP_BAD_REQUEST);
    }
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return new Response('No project found for this id', Response::HTTP_NOT_FOUND);
    }
    $source_language = $request->query->get('source_language');
    $source_language = is_null($source_language) ? $source_language : (string) $source_language;
    $target_language = (string) $request->query->get('target_language');
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
      '_cache' => $translation_result[0]->cache,
    ]);
  }

  #[Route(path: '/translate/custom/project/{id}', name: 'project_custom_translation', methods: ['PUT', 'GET', 'DELETE'])]
  public function projectCustomTranslationAction(Request $request, string $id): Response
  {
    return match ($request->getMethod()) {
      'PUT' => $this->projectCustomTranslationPutAction($request, $id),
      'GET' => $this->projectCustomTranslationGetAction($request, $id),
      'DELETE' => $this->projectCustomTranslationDeleteAction($request, $id),
      default => new Response(null, Response::HTTP_BAD_REQUEST),
    };
  }

  #[Route(path: '/translate/custom/project/{id}/list', name: 'project_custom_translation_language_list', methods: ['GET'])]
  public function projectCustomTranslationLanguageListAction(string $id, ProjectCustomTranslationRepository $repository): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    return new JsonResponse($repository->listDefinedLanguages($project));
  }

  /**
   * @throws NonUniqueResultException
   */
  #[Route(path: 'program/comment/{id}', name: 'program_comment', methods: ['GET'])]
  public function projectCommentDetail(string $id): Response
  {
    $arr_comment = $this->comment_repository->getProjectCommentDetailViewData($id);
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($arr_comment['program_id'] ?? null);
    if (null === $project) {
      return $this->redirectToIndexOnError();
    }
    $comment_list = $this->comment_repository->getProjectCommentDetailViewCommentListData($id);
    array_unshift($comment_list, $arr_comment);

    return $this->render('Project/Comment/comment_detail.html.twig', [
      'comment' => $arr_comment,
      'replies' => $comment_list,
      'isAdmin' => $this->isGranted('ROLE_ADMIN'),
      'project' => $project,
      'are_replies' => true,
    ]);
  }

  protected function redirectToIndexOnError(): RedirectResponse
  {
    $this->addFlash('snackbar', $this->translator->trans('snackbar.project_not_found', [], 'catroweb'));

    return $this->redirectToRoute('index');
  }

  private function checkAndAddViewed(Request $request, Program $project, array $viewed): void
  {
    if (!in_array($project->getId(), $viewed, true)) {
      $this->project_manager->increaseViews($project);
      $viewed[] = $project->getId();
      $request->getSession()->set('viewed', $viewed);
    }
  }

  private function createProjectDetailsArray(Program $project,
    array $active_like_types,
    array $active_user_like_types,
    int $total_like_count,
    ?string $referrer,
    array $project_comments): array
  {
    $url = $this->generateUrl('open_api_server_projects_projectidcatrobatget', ['id' => $project->getId()]);

    return [
      'screenshotBig' => $this->screenshot_repository->getScreenshotWebPath($project->getId()),
      'downloadUrl' => $url,
      'languageVersion' => $project->getLanguageVersion(),
      'downloads' => $project->getDownloads() + $project->getApkDownloads(),
      'views' => $project->getViews(),
      'filesize' => sprintf('%.2f', $project->getFilesize() / 1_048_576),
      'age' => $this->elapsed_time->format($project->getUploadedAt()->getTimestamp()),
      'referrer' => $referrer,
      'id' => $project->getId(),
      'comments' => $project_comments,
      'activeLikeTypes' => $active_like_types,
      'activeUserLikeTypes' => $active_user_like_types,
      'totalLikeCount' => $total_like_count,
      'isAdmin' => $this->isGranted('ROLE_ADMIN'),
    ];
  }

  private function projectCustomTranslationDeleteAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->project_manager->find($id);
    if (null === $project || $project->getUser() !== $user) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    $field = (string) $request->query->get('field');
    $language = (string) $request->query->get('language');

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
          return new Response(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (\InvalidArgumentException $exception) {
      return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }

  private function projectCustomTranslationGetAction(Request $request, string $id): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    $field = (string) $request->query->get('field');
    $language = (string) $request->query->get('language');

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
          return new Response(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (\InvalidArgumentException $exception) {
      return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response($result, null == $result ? Response::HTTP_NOT_FOUND : Response::HTTP_OK);
  }

  private function projectCustomTranslationPutAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->project_manager->find($id);
    if (null === $project || $project->getUser() !== $user) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')
      || !$request->query->has('text')) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    $field = (string) $request->query->get('field');
    $language = (string) $request->query->get('language');
    $text = (string) $request->query->get('text');

    if ('' === trim($text)) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
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
          return new Response(null, Response::HTTP_BAD_REQUEST);
      }
    } catch (\InvalidArgumentException $exception) {
      return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }
}
