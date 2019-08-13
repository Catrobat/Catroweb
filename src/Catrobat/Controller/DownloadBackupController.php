<?php

namespace App\Catrobat\Controller;

use App\Catrobat\Services\BackupFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class DownloadBackupController extends AbstractController
{
  /**
   * @Route("/download-backup/{backupFile}", name="backup_download", methods={"GET"})
   *
   * @param Request $request
   * @param $backupFile
   * @param BackupFileRepository $backupFileRepository
   *
   * @return BinaryFileResponse
   */
  public function downloadBackupAction(Request $request, $backupFile, BackupFileRepository $backupFileRepository)
  {

    $file = $backupFileRepository->getBackupFile($backupFile);
    if ($file->isFile())
    {
      $response = new BinaryFileResponse($file);
      $d = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $backupFile
      );
      $response->headers->set('Content-Disposition', $d);

      return $response;
    }
    throw new NotFoundHttpException();
  }
}