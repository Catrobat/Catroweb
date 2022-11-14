<?php

namespace App\Application\Controller\Ci;

use App\DB\Entity\Project\Program;
use App\Project\Apk\ApkRepository;
use App\Project\Apk\JenkinsDispatcher;
use App\Project\ProgramManager;
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
    private readonly ProgramManager $program_manager,
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
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);
    if (null === $program || !$program->isVisible()) {
      throw $this->createNotFoundException();
    }
    if (Program::APK_READY === $program->getApkStatus()) {
      return new JsonResponse(['status' => 'ready']);
    }
    if (Program::APK_PENDING === $program->getApkStatus()) {
      return new JsonResponse(['status' => 'pending']);
    }
    $this->dispatcher->sendBuildRequest($program->getId());
    $program->setApkStatus(Program::APK_PENDING);
    $program->setApkRequestTime(TimeUtils::getDateTime());
    $this->program_manager->save($program);

    return new JsonResponse(['status' => 'pending']);
  }

  #[Route(path: '/ci/upload/{id}', name: 'ci_upload_apk', defaults: ['_format' => 'json'], methods: ['GET', 'POST'])]
  public function uploadApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);
    if (null === $program || !$program->isVisible()) {
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
    $this->apk_repository->save($file, $program->getId());
    $program->setApkStatus(Program::APK_READY);
    $this->program_manager->save($program);

    return new JsonResponse(['result' => 'success']);
  }

  #[Route(path: '/ci/failed/{id}', name: 'ci_failed_apk', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function failedApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);
    if (null === $program || !$program->isVisible()) {
      throw $this->createNotFoundException();
    }
    $config = $this->arr_jenkins_config;
    if ($request->query->get('token') !== $config['uploadtoken']) {
      throw new AccessDeniedException();
    }
    if (Program::APK_PENDING === $program->getApkStatus()) {
      $program->setApkStatus(Program::APK_NONE);
      $this->program_manager->save($program);

      return new JsonResponse(['OK']);
    }

    return new JsonResponse(['error' => 'program is not building']);
  }
}
