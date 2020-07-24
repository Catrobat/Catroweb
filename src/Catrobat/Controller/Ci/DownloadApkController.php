<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DownloadApkController extends AbstractController
{
  /**
   * @Route("/ci/download/{id}", name="ci_download", methods={"GET"})
   */
  public function downloadApkAction(Request $request, Program $program, ApkRepository $apk_repository,
                                    ProgramManager $programManager): BinaryFileResponse
  {
    if (!$program->isVisible())
    {
      throw new NotFoundHttpException();
    }
    if (Program::APK_READY != $program->getApkStatus())
    {
      throw new NotFoundHttpException();
    }

    try
    {
      $file = $apk_repository->getProgramFile($program->getId());
    }
    catch (Exception $exception)
    {
      throw new NotFoundHttpException($exception->__toString());
    }
    if ($file->isFile())
    {
      $downloaded = $request->getSession()->get('apk_downloaded', []);
      if (!in_array($program->getId(), $downloaded, true))
      {
        $programManager->increaseApkDownloads($program);
        $downloaded[] = $program->getId();
        $request->getSession()->set('apk_downloaded', $downloaded);
      }

      return $this->createBinaryFileResponse($program, $file);
    }

    throw new NotFoundHttpException();
  }

  private function createBinaryFileResponse(Program $program, File $file): BinaryFileResponse
  {
    $response = new BinaryFileResponse($file);
    $d = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $program->getId().'.apk'
    );
    $response->headers->set('Content-Disposition', $d);
    $response->headers->set('Content-type', 'application/vnd.android.package-archive');

    return $response;
  }
}
