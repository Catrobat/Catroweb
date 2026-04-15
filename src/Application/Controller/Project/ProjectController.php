<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\DB\Enum\ContentType;
use App\Moderation\ContentVisibilityManager;
use App\Project\Event\CheckScratchProjectEvent;
use App\Project\ProjectManager;
use App\Project\ProjectStatisticsService;
use App\Storage\ScreenshotRepository;
use App\Translation\TranslationDelegate;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectController extends AbstractController
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly TranslatorInterface $translator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly EventDispatcherInterface $event_dispatcher,
    private readonly TranslationDelegate $translation_delegate,
    private readonly UserCommentRepository $comment_repository,
    private readonly ProjectCustomTranslationRepository $projectCustomTranslationRepository,
    private readonly ContentVisibilityManager $content_visibility_manager,
    private readonly ProjectStatisticsService $project_statistics_service,
  ) {
  }

  #[Route(path: '/projects', name: 'projects_browse', methods: ['GET'])]
  public function projectsBrowse(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    return $this->render('Project/ProjectsBrowse.html.twig', [
      'is_logged_in' => null !== $user,
      'user_id' => null !== $user ? ($user->getId() ?? '') : '',
    ]);
  }

  #[Route(path: '/project/upload', name: 'project_upload', methods: ['GET'], priority: 10)]
  public function projectUpload(): Response
  {
    if (!$this->getUser()) {
      return $this->redirectToRoute('login');
    }

    return $this->render('Project/ProjectUpload.html.twig');
  }

  #[Route(path: '/project/{id}', name: 'project', defaults: ['id' => 0])]
  #[Route(path: '/program/{id}', name: 'project_deprecated')]
  #[Route(path: '/details/{id}', name: 'catrobat_web_detail', methods: ['GET'])]
  public function project(Request $request, string $id): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $this->addFlash('snackbar', $this->translator->trans('snackbar.project_not_found', [], 'catroweb'));

      return $this->redirectToRoute('index');
    }

    $projectId = $project->getId() ?? throw new \RuntimeException('Project ID must not be null');

    if ($project->isScratchProgram()) {
      $scratchId = $project->getScratchId();
      if (null !== $scratchId) {
        $this->event_dispatcher->dispatch(new CheckScratchProjectEvent($scratchId));
      }
    }

    $viewed = $request->getSession()->get('viewed', []);
    $this->checkAndAddViewed($request, $project, $viewed);
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);
    /** @var User|null $user */
    $user = $this->getUser();
    $logged_in = null !== $user;
    $my_project = $logged_in && $project->getUser() === $user;

    return $this->render('Project/ProjectPage.html.twig', [
      'project' => $project,
      'project_id' => $projectId,
      'project_name' => $project->getName(),
      'project_description' => $project->getDescription(),
      'screenshot_big' => $this->screenshot_repository->getScreenshotWebPath($projectId),
      'my_project' => $my_project,
      'logged_in' => $logged_in,
      'is_whitelisted' => $this->content_visibility_manager->isWhitelisted(ContentType::Project, $projectId),
      'auto_hidden' => $project->getAutoHidden(),
      'max_name_size' => ProjectsRequestValidator::MAX_NAME_LENGTH,
      'max_description_size' => ProjectsRequestValidator::MAX_DESCRIPTION_LENGTH,
      'extracted_path' => $this->parameter_bag->get('catrobat.file.extract.path'),
    ]);
  }

  #[Route(path: '/translate/project/{id}', name: 'translate_project', methods: ['GET'])]
  public function translateProject(Request $request, string $id): Response
  {
    if (!$request->query->has('target_language')) {
      return new Response('Target language is required', Response::HTTP_BAD_REQUEST);
    }

    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
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

    $title_translation = $translation_result[0] ?? null;
    if (null === $title_translation) {
      return new Response('Translation unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
    }

    return $response->setData([
      'id' => $project->getId(),
      'source_language' => $source_language ?? $title_translation->detected_source_language,
      'target_language' => $target_language,
      'translated_title' => $title_translation->translation,
      'translated_description' => $translation_result[1]?->translation,
      'translated_credit' => $translation_result[2]?->translation,
      'provider' => $title_translation->provider,
      '_cache' => $title_translation->cache,
    ]);
  }

  #[Route(path: '/translate/custom/project/{id}', name: 'project_custom_translation', methods: ['PUT', 'GET', 'DELETE'])]
  public function projectCustomTranslation(Request $request, string $id): Response
  {
    return match ($request->getMethod()) {
      'PUT' => $this->projectCustomTranslationPutAction($request, $id),
      'GET' => $this->projectCustomTranslationGetAction($request, $id),
      'DELETE' => $this->projectCustomTranslationDeleteAction($request, $id),
      default => new Response(null, Response::HTTP_BAD_REQUEST),
    };
  }

  #[Route(path: '/translate/custom/project/{id}/list', name: 'project_custom_translation_language_list', methods: ['GET'])]
  public function projectCustomTranslationLanguageList(string $id): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    return new JsonResponse($this->projectCustomTranslationRepository->listDefinedLanguages($project));
  }

  /**
   * @throws NonUniqueResultException
   */
  #[Route(path: 'project/comment/{id}', name: 'project_comment', methods: ['GET'])]
  public function projectCommentDetail(string $id): Response
  {
    $arr_comment = $this->comment_repository->getProjectCommentDetailViewData($id);
    if (!isset($arr_comment['program_id'])) {
      return $this->redirectToIndexOnError();
    }

    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($arr_comment['program_id']);
    if (!$project instanceof Project) {
      return $this->redirectToIndexOnError();
    }

    $is_hidden = (bool) ($arr_comment['is_reported'] ?? false);
    if ($is_hidden && !$this->isGranted('ROLE_ADMIN')) {
      $current_user = $this->getUser();
      $is_owner = $current_user instanceof User
        && isset($arr_comment['user_id'])
        && $current_user->getId() === (string) $arr_comment['user_id'];

      if (!$is_owner) {
        return $this->redirectToIndexOnError();
      }
    }

    $comment_list = [];

    return $this->render('Project/Comment/Detail.html.twig', [
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

  private function checkAndAddViewed(Request $request, Project $project, array $viewed): void
  {
    if (!in_array($project->getId(), $viewed, true)) {
      $this->project_statistics_service->increaseViews($project);
      $viewed[] = $project->getId();
      $request->getSession()->set('viewed', $viewed);
    }
  }

  private function projectCustomTranslationDeleteAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (!$user instanceof UserInterface) {
      return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->project_manager->find($id);
    if (!$project instanceof Project || $project->getUser() !== $user) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    $field = (string) $request->query->get('field');
    $language = (string) $request->query->get('language');

    $result = false;
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
    } catch (\InvalidArgumentException $invalidArgumentException) {
      return new Response($invalidArgumentException->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }

  private function projectCustomTranslationGetAction(Request $request, string $id): Response
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      return new Response(null, Response::HTTP_NOT_FOUND);
    }

    if (!$request->query->has('field')
      || !$request->query->has('language')) {
      return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    $field = (string) $request->query->get('field');
    $language = (string) $request->query->get('language');

    $result = null;
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
    } catch (\InvalidArgumentException $invalidArgumentException) {
      return new Response($invalidArgumentException->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response($result, null == $result ? Response::HTTP_NOT_FOUND : Response::HTTP_OK);
  }

  private function projectCustomTranslationPutAction(Request $request, string $id): Response
  {
    $user = $this->getUser();
    if (!$user instanceof UserInterface) {
      return new Response(null, Response::HTTP_UNAUTHORIZED);
    }

    $project = $this->project_manager->find($id);
    if (!$project instanceof Project || $project->getUser() !== $user) {
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

    $result = false;
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
    } catch (\InvalidArgumentException $invalidArgumentException) {
      return new Response($invalidArgumentException->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    return new Response(null, $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
  }
}
