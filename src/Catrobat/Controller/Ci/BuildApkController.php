<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Entity\ProgramManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Class BuildApkController
 * @package App\Catrobat\Controller\Ci
 */
class BuildApkController extends AbstractController
{

  /**
   * @Route("/ci/build/{id}", name="ci_build", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Program $program
   * @param JenkinsDispatcher $dispatcher
   * @param ProgramManager $program_manager
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function createApkAction(Program $program, JenkinsDispatcher $dispatcher, ProgramManager $program_manager)
  {
    if (!$program->isVisible())
    {
      throw $this->createNotFoundException();
    }

    if ($program->getApkStatus() === Program::APK_READY)
    {
      return JsonResponse::create(['status' => 'ready']);
    }
    elseif ($program->getApkStatus() === Program::APK_PENDING)
    {
      return JsonResponse::create(['status' => 'pending']);
    }

    $dispatcher->sendBuildRequest($program->getId());

    $program->setApkStatus(Program::APK_PENDING);
    $program->setApkRequestTime(new \DateTime());
    $program_manager->save($program);

    return JsonResponse::create(['status' => 'pending']);
  }

  /**
   * @Route("/ci/upload/{id}", name="ci_upload_apk", defaults={"_format": "json"}, methods={"GET", "POST"})
   *
   * @param Request $request
   * @param Program $program
   * @param ApkRepository $apk_repository
   * @param ProgramManager $program_manager
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function uploadApkAction(Request $request, Program $program,
                                  ApkRepository $apk_repository, ProgramManager $program_manager)
  {
    /**
     * @var $file File
     */
    $config = $this->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken'])
    {
      throw new AccessDeniedException();
    }
    elseif ($request->files->count() != 1)
    {
      throw new BadRequestHttpException('Wrong number of files: ' . $request->files->count());
    }
    else
    {
      $file = array_values($request->files->all())[0];
      $apk_repository->save($file, $program->getId());
      $program->setApkStatus(Program::APK_READY);
      $program_manager->save($program);
    }

    return JsonResponse::create(['result' => 'success']);
  }


  /**
   * @Route("/ci/failed/{id}", name="ci_failed_apk", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   * @param Program $program
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function failedApkAction(Request $request, Program $program,  ProgramManager $program_manager)
  {
    $config = $this->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken'])
    {
      throw new AccessDeniedException();
    }
    if ($program->getApkStatus() === Program::APK_PENDING)
    {
      $program->setApkStatus(Program::APK_NONE);
      $program_manager->save($program);

      return JsonResponse::create(['OK']);
    }

    return JsonResponse::create(['error' => 'program is not building']);
  }
}
