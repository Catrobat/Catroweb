<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Entity\ProgramManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class DownloadApkController extends AbstractController
{

  /**
   * @Route("/ci/download/{id}", name="ci_download", methods={"GET"})
   *
   * @param Request $request
   * @param Program $program
   * @param ApkRepository $apk_repository
   * @param ProgramManager $programManager
   *
   * @return BinaryFileResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function downloadApkAction(Request $request, Program $program, ApkRepository $apk_repository,
                                    ProgramManager $programManager)
  {
    if (!$program->isVisible())
    {
      throw new NotFoundHttpException();
    }
    if ($program->getApkStatus() != Program::APK_READY)
    {
      throw new NotFoundHttpException();
    }

    try
    {
      $file = $apk_repository->getProgramFile($program->getId());
    } catch (\Exception $e)
    {
      throw new NotFoundHttpException();
    }
    if ($file->isFile())
    {

      $downloaded = $request->getSession()->get('apk_downloaded', []);
      if (!in_array($program->getId(), $downloaded))
      {
        $programManager->increaseApkDownloads($program);
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
