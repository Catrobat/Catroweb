<?php

namespace App\Application\Controller\Ci;

use App\DB\Entity\Project\Project;
use App\Project\Apk\ApkRepository;
use App\Project\Apk\JenkinsDispatcher;
use App\Project\ProjectManager;
use App\Utils\TimeUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BuildApkController.
 *
 * @deprecated - Move to Catroweb-API
 */
class BuildApkController extends AbstractController
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly JenkinsDispatcher $dispatcher,
    private readonly ApkRepository $apk_repository,
    private readonly array $arr_jenkins_config)
  {
  }

  /**
   * @throws \Exception
   */
  #[Route(path: '/ci/build/{id}', name: 'ci_build', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function createApkAction(string $id): JsonResponse
  {
    /** @var Project|null $project */
    $project = $this->project_manager->find($id);
    if (null === $project || !$project->isVisible()) {
      throw $this->createNotFoundException();
    }
    if (Project::APK_READY === $project->getApkStatus()) {
      return new JsonResponse(['status' => 'ready']);
    }
    if (Project::APK_PENDING === $project->getApkStatus()) {
      return new JsonResponse(['status' => 'pending']);
    }
    $this->dispatcher->sendBuildRequest($project->getId());
    $project->setApkStatus(Project::APK_PENDING);
    $project->setApkRequestTime(TimeUtils::getDateTime());
    $this->project_manager->save($project);

    return new JsonResponse(['status' => 'pending']);
  }

  #[Route(path: '/ci/upload/{id}', name: 'ci_upload_apk', defaults: ['_format' => 'json'], methods: ['GET', 'POST'])]
  public function uploadApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Project|null $project */
    $project = $this->project_manager->find($id);
    if (null === $project || !$project->isVisible()) {
      throw $this->createNotFoundException();
    }
    $config = $this->arr_jenkins_config;
    if ($request->query->get('token') !== $config['uploadtoken']) {
      throw new AccessDeniedException();
    }
    if (1 != $request->files->count()) {
      throw new BadRequestHttpException('Wrong number of files: '.$request->files->count());
    }
    $file = array_values($request->files->all())[0];
    $this->apk_repository->save($file, $project->getId());
    $project->setApkStatus(Project::APK_READY);
    $this->project_manager->save($project);

    return new JsonResponse(['result' => 'success']);
  }

  #[Route(path: '/ci/failed/{id}', name: 'ci_failed_apk', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function failedApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Project|null $project */
    $project = $this->project_manager->find($id);
    if (null === $project || !$project->isVisible()) {
      throw $this->createNotFoundException();
    }
    $config = $this->arr_jenkins_config;
    if ($request->query->get('token') !== $config['uploadtoken']) {
      throw new AccessDeniedException();
    }
    if (Project::APK_PENDING === $project->getApkStatus()) {
      $project->setApkStatus(Project::APK_NONE);
      $this->project_manager->save($project);

      return new JsonResponse(['OK']);
    }

    return new JsonResponse(['error' => 'project is not building']);
  }
}
