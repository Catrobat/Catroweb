<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BuildApkController extends AbstractController
{
  /**
   * @Route("/ci/build/{id}", name="ci_build", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
   */
  public function createApkAction(Program $program, JenkinsDispatcher $dispatcher, ProgramManager $program_manager): JsonResponse
  {
    if (!$program->isVisible())
    {
      throw $this->createNotFoundException();
    }

    if (Program::APK_READY === $program->getApkStatus())
    {
      return JsonResponse::create(['status' => 'ready']);
    }
    if (Program::APK_PENDING === $program->getApkStatus())
    {
      return JsonResponse::create(['status' => 'pending']);
    }

    $dispatcher->sendBuildRequest($program->getId());

    $program->setApkStatus(Program::APK_PENDING);
    $program->setApkRequestTime(TimeUtils::getDateTime());

    $program_manager->save($program);

    return JsonResponse::create(['status' => 'pending']);
  }

  /**
   * @Route("/ci/upload/{id}", name="ci_upload_apk", defaults={"_format": "json"}, methods={"GET", "POST"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function uploadApkAction(Request $request, Program $program,
                                  ApkRepository $apk_repository, ProgramManager $program_manager): JsonResponse
  {
    $config = $this->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken'])
    {
      throw new AccessDeniedException();
    }
    if (1 != $request->files->count())
    {
      throw new BadRequestHttpException('Wrong number of files: '.$request->files->count());
    }

    $file = array_values($request->files->all())[0];
    $apk_repository->save($file, $program->getId());
    $program->setApkStatus(Program::APK_READY);
    $program_manager->save($program);

    return JsonResponse::create(['result' => 'success']);
  }

  /**
   * @Route("/ci/failed/{id}", name="ci_failed_apk", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function failedApkAction(Request $request, Program $program, ProgramManager $program_manager): JsonResponse
  {
    $config = $this->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken'])
    {
      throw new AccessDeniedException();
    }
    if (Program::APK_PENDING === $program->getApkStatus())
    {
      $program->setApkStatus(Program::APK_NONE);
      $program_manager->save($program);

      return JsonResponse::create(['OK']);
    }

    return JsonResponse::create(['error' => 'program is not building']);
  }
}
