<?php

namespace App\Api_deprecated\Controller;

use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class DownloadMediaPackageController extends AbstractController
{
  public function __construct(private readonly EntityManagerInterface $entity_manager)
  {
  }

  #[Route(path: '/download-media/{id}', name: 'download_media', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function downloadMediaPackageAction(int $id, MediaPackageFileRepository $file_repository): BinaryFileResponse
  {
    /** @var MediaPackageFile|null $media_file */
    $media_file = $this->entity_manager->getRepository(MediaPackageFile::class)->find($id);
    if (null === $media_file) {
      throw new NotFoundHttpException();
    }
    $file = $file_repository->getMediaFile($id, $media_file->getExtension());
    if ($file->isFile()) {
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
