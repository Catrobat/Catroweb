<?php

namespace App\Catrobat\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Program;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class DownloadApkController
 * @package App\Catrobat\Controller\Ci
 */
class DownloadApkController extends Controller
{

  /**
   * @Route("/ci/download/{id}", name="ci_download", requirements={"id": "\d+"}, methods={"GET"})
   *
   * @param Request $request
   * @param Program $program
   *
   * @return BinaryFileResponse
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function downloadApkAction(Request $request, Program $program)
  {
    /* @var $apkrepository \App\Catrobat\Services\ApkRepository */

    if (!$program->isVisible())
    {
      throw new NotFoundHttpException();
    }
    if ($program->getApkStatus() != Program::APK_READY)
    {
      throw new NotFoundHttpException();
    }

    $apkrepository = $this->get('apkrepository');

    try
    {
      $file = $apkrepository->getProgramFile($program->getId());
    } catch (\Exception $e)
    {
      throw new NotFoundHttpException();
    }
    if ($file->isFile())
    {

      $downloaded = $request->getSession()->get('apk_downloaded', []);
      if (!in_array($program->getId(), $downloaded))
      {
        $this->get('programmanager')->increaseApkDownloads($program);
        $downloaded[] = $program->getId();
        $request->getSession()->set('apk_downloaded', $downloaded);
      }

      $response = $this->createBinaryFileResponse($program, $file);

      return $response;
    }

    throw new NotFoundHttpException();
  }


  /**
   * @param Program $program
   * @param         $file
   *
   * @return BinaryFileResponse
   */
  private function createBinaryFileResponse(Program $program, $file)
  {
    $response = new BinaryFileResponse($file);
    $d = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $program->getId() . '.apk'
    );
    $response->headers->set('Content-Disposition', $d);
    $response->headers->set('Content-type', 'application/vnd.android.package-archive');

    return $response;
  }
}
