<?php

namespace App\Catrobat\Controller;

use App\Entity\MediaPackageFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class DownloadMediaPackageController
 * @package App\Catrobat\Controller
 */
class DownloadMediaPackageController extends Controller
{

  /**
   * @Route("/download-media/{id}", name="download_media", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   * @param         $id
   *
   * @return BinaryFileResponse
   */
  public function downloadMediaPackageAction(Request $request, $id)
  {
    /**
     * @var $file_repository \App\Catrobat\Services\MediaPackageFileRepository
     * @var $media_file      \App\Entity\MediaPackageFile
     */

    $file_repository = $this->get('mediapackagefilerepository');

    $em = $this->getDoctrine()->getManager();
    $media_file = $em->getRepository(MediaPackageFile::class)->findOneBy(['id' => $id]);

    if (!$media_file)
    {
      throw new NotFoundHttpException();
    }

    $file = $file_repository->getMediaFile($id, $media_file->getExtension());
    if ($file->isFile())
    {
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
