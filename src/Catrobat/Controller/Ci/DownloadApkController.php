<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DownloadApkController.
 */
class DownloadApkController extends AbstractController
{
  /**
   * @Route("/ci/download/{id}", name="ci_download", methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @return BinaryFileResponse
   */
  public function downloadApkAction(Request $request, Program $program, ApkRepository $apk_repository,
                                    ProgramManager $programManager)
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
    catch (\Exception $e)
    {
      throw new NotFoundHttpException();
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

  /**
   * @param $file
   *
   * @return BinaryFileResponse
   */
  private function createBinaryFileResponse(Program $program, $file)
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
