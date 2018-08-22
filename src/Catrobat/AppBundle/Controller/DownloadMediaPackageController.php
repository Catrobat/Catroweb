<?php

namespace Catrobat\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Catrobat\AppBundle\Services\ProgramFileRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadMediaPackageController extends Controller
{
    /**
     * @Route("/download-media/{id}", name="download_media", defaults={"_format": "json"}, methods={"GET"})
     */
    public function downloadMediaPackageAction(Request $request, $id) {
        /**
         * @var $file_repository \Catrobat\AppBundle\Services\MediaPackageFileRepository
         * @var $media_file \Catrobat\AppBundle\Entity\MediaPackageFile
         */

        $file_repository = $this->get('mediapackagefilerepository');

        $em = $this->getDoctrine()->getManager();
        $media_file = $em->getRepository("\Catrobat\AppBundle\Entity\MediaPackageFile")->findOneBy(array('id' => $id));

        if (!$media_file) {
            throw new NotFoundHttpException();
        }

        $file = $file_repository->getMediaFile($id, $media_file->getExtension());
        if ($file->isFile()) {
            $media_file->setDownloads($media_file->getDownloads() + 1);
            $em->persist($media_file);
            $em->flush();

            $response = new BinaryFileResponse($file);
            $d = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $media_file->getId() . '.' . $media_file->getExtension()
            );
            $response->headers->set('Content-Disposition', $d);

            return $response;
        }
        throw new NotFoundHttpException();
    }
}
