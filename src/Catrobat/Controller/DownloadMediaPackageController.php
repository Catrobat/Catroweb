<?php

namespace App\Catrobat\Controller;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackageFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DownloadMediaPackageController extends AbstractController
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }

  /**
   * @Route("/download-media/{id}", name="download_media", defaults={"_format": "json"}, methods={"GET"})
   */
  public function downloadMediaPackageAction(int $id, MediaPackageFileRepository $file_repository): BinaryFileResponse
  {
    /** @var MediaPackageFile|null $media_file */
    $media_file = $this->entity_manager->getRepository(MediaPackageFile::class)->find($id);

    if (null === $media_file)
    {
      throw new NotFoundHttpException();
    }

    $file = $file_repository->getMediaFile($id, $media_file->getExtension());
    if ($file->isFile())
    {
      $media_file->setDownloads($media_file->getDownloads() + 1);
      $this->entity_manager->persist($media_file);
      $this->entity_manager->flush();

      $response = new BinaryFileResponse($file);

      // replace special characters in filename and replace them with -
      $filename = preg_replace('#[^A-Za-z0-9-_. ()]#', '-', $media_file->getName());
      // replace multiple following - with a single one
      $filename = preg_replace('#-+#', '-', $filename);

      $d = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $filename.'.'.$media_file->getExtension()
      );
      $response->headers->set('Content-Disposition', $d);

      return $response;
    }
    throw new NotFoundHttpException();
  }
}
