<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\DB\Enum\ContentType;
use App\Moderation\ContentVisibilityManager;
use App\Project\Event\CheckScratchProjectEvent;
use App\Project\ProjectManager;
use App\Project\ProjectStatisticsService;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectController extends AbstractController
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly TranslatorInterface $translator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly EventDispatcherInterface $event_dispatcher,
    private readonly UserCommentRepository $comment_repository,
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
}
