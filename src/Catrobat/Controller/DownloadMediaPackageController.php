<?php

namespace App\Catrobat\Controller;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackageFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class DownloadMediaPackageController
 * @package App\Catrobat\Controller
 */
class DownloadMediaPackageController extends AbstractController
{

  /**
   * @Route("/download-media/{id}", name="download_media", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   * @param $id
   * @param MediaPackageFileRepository $file_repository
   *
   * @return BinaryFileResponse
   */
  public function downloadMediaPackageAction(Request $request, $id, MediaPackageFileRepository $file_repository)
  {
    /**
     * @var $media_file  MediaPackageFile
     */

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

      // replace special characters in filename and replace them with -
      $filename = preg_replace('/[^A-Za-z0-9-_. ()]/', '-', $media_file->getName());
      // replace multiple following - with a single one
      $filename = preg_replace('/-+/', '-', $filename);

      $d = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $filename . '.' . $media_file->getExtension()
      );
      $response->headers->set('Content-Disposition', $d);

      return $response;
    }
    throw new NotFoundHttpException();
  }
}
