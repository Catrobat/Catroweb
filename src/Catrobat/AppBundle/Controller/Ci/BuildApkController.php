<?php

namespace Catrobat\AppBundle\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BuildApkController extends Controller
{
  /**
   * @Route("/ci/build/{id}", name="ci_build", defaults={"_format": "json"}, requirements={"id": "\d+"}, methods={"GET"})
   */
  public function createApkAction(Request $request, Program $program)
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

    $dispatcher = $this->get('ci.jenkins.dispatcher');
    $dispatcher->sendBuildRequest($program->getId());

    $program->setApkStatus(Program::APK_PENDING);
    $program->setApkRequestTime(new \DateTime());
    $this->get('programmanager')->save($program);

    return JsonResponse::create(['status' => 'pending']);
  }

  /**
   * @Route("/ci/upload/{id}", name="ci_upload_apk", defaults={"_format": "json"}, requirements={"id": "\d+"}, methods={"GET", "POST"})
   */
  public function uploadApkAction(Request $request, Program $program)
  {
    /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */

    $config = $this->container->getParameter('jenkins');
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
      $apkrepository = $this->get('apkrepository');
      $apkrepository->save($file, $program->getId());
      $program->setApkStatus(Program::APK_READY);
      $this->get('programmanager')->save($program);
    }

    return JsonResponse::create(['result' => 'success']);
  }

  /**
   * @Route("/ci/failed/{id}", name="ci_failed_apk", defaults={"_format": "json"}, requirements={"id": "\d+"}, methods={"GET"})
   */
  public function failedApkAction(Request $request, Program $program)
  {
    $config = $this->container->getParameter('jenkins');
    if ($request->query->get('token') !== $config['uploadtoken'])
    {
      throw new AccessDeniedException();
    }
    if ($program->getApkStatus() === Program::APK_PENDING)
    {
      $program->setApkStatus(Program::APK_NONE);
      $this->get('programmanager')->save($program);

      return JsonResponse::create(['OK']);
    }

    return JsonResponse::create(['error' => 'program is not building']);
  }
}
