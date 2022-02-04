<?php

namespace App\Controller\Ci;

use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Entity\Program;
use App\Manager\ProgramManager;
use App\Repository\ApkRepository;
use App\Utils\TimeUtils;
use Exception;
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
  private ProgramManager $program_manager;

  private JenkinsDispatcher $dispatcher;

  private ApkRepository $apk_repository;

  public function __construct(ProgramManager $program_manager, JenkinsDispatcher $dispatcher,
                              ApkRepository $apk_repository)
  {
    $this->program_manager = $program_manager;
    $this->dispatcher = $dispatcher;
    $this->apk_repository = $apk_repository;
  }

  /**
   * @Route("/ci/build/{id}", name="ci_build", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws Exception
   */
  public function createApkAction(string $id): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);

    if (null === $program || !$program->isVisible()) {
      throw $this->createNotFoundException();
    }

    if (Program::APK_READY === $program->getApkStatus()) {
      return JsonResponse::create(['status' => 'ready']);
    }
    if (Program::APK_PENDING === $program->getApkStatus()) {
      return JsonResponse::create(['status' => 'pending']);
    }

    $this->dispatcher->sendBuildRequest($program->getId());

    $program->setApkStatus(Program::APK_PENDING);
    $program->setApkRequestTime(TimeUtils::getDateTime());

    $this->program_manager->save($program);

    return JsonResponse::create(['status' => 'pending']);
  }

  /**
   * @Route("/ci/upload/{id}", name="ci_upload_apk", defaults={"_format": "json"}, methods={"GET", "POST"})
   */
  public function uploadApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);

    if (null === $program || !$program->isVisible()) {
      throw $this->createNotFoundException();
    }

    $config = $this->getParameter('jenkins');
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

    return JsonResponse::create(['result' => 'success']);
  }

  /**
   * @Route("/ci/failed/{id}", name="ci_failed_apk", defaults={"_format": "json"}, methods={"GET"})
   */
  public function failedApkAction(string $id, Request $request): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);

    if (null === $program || !$program->isVisible()) {
      throw $this->createNotFoundException();
    }

    $config = $this->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken']) {
      throw new AccessDeniedException();
    }
    if (Program::APK_PENDING === $program->getApkStatus()) {
      $program->setApkStatus(Program::APK_NONE);
      $this->program_manager->save($program);

      return JsonResponse::create(['OK']);
    }

    return JsonResponse::create(['error' => 'program is not building']);
  }
}
