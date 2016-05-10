<?php

namespace Catrobat\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadBackupController extends Controller
{
    /**
     * @Route("/download-backup/{backupFile}", name="backup_download")
     * @Method({"GET"})
     */
    public function downloadBackupAction(Request $request, $backupFile)
    {
        /* @var $backupFileRepository \Catrobat\AppBundle\Services\BackupFileRepository */
        $backupFileRepository = $this->get('backupfilerepository');

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