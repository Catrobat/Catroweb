<?php

namespace App\Api;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Utils\APIHelper;
use App\Utils\APIQueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  private EntityManagerInterface $entity_manager;

  private UrlGeneratorInterface $url_generator;

  private MediaPackageFileRepository $media_package_file_repository;

  private RequestStack $stack;

  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator,
                              MediaPackageFileRepository $media_package_file_repository, RequestStack $stack)
  {
    $this->entity_manager = $entity_manager;
    $this->url_generator = $url_generator;
    $this->media_package_file_repository = $media_package_file_repository;
    $this->stack = $stack;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesSearchGet(string $query, ?string $flavor = null, ?int $limit = 20, ?int $offset = 0, ?string $package_name = null, &$responseCode, array &$responseHeaders)
  {
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $responseCode = Response::HTTP_OK;

    $found_media_files = $this->media_package_file_repository->search($query, $flavor, $package_name, $limit, $offset);

    return $this->getMediaFilesDataResponse($found_media_files);
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackageNameGet(string $name, ?int $limit = 20, ?int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $name])
    ;

    if (null === $media_package)
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    $media_package_categories = $media_package->getCategories();
    if (empty($media_package_categories))
    {
      return [];
    }

    $json_response_array = [];

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category)
    {
      $media_package_files = $media_package_category->getFiles();
      if ((0 != $offset && count($media_package_files) <= $offset) || count($json_response_array) === $limit)
      {
        if (0 != $offset)
        {
          $offset -= count($media_package_files);
        }
        continue;
      }

      /** @var MediaPackageFile $media_package_file */
      foreach ($media_package_files as $media_package_file)
      {
        if (0 != $offset)
        {
          --$offset;
          continue;
        }
        if (count($json_response_array) === $limit)
        {
          break;
        }
        $json_response_array[] = new MediaFileResponse($this->getMediaFileDataResponse($media_package_file, $media_package));
      }
    }

    return $json_response_array;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFileIdGet(int $id, &$responseCode, array &$responseHeaders)
  {
    $media_package_file = $this->entity_manager->getRepository(MediaPackageFile::class)
      ->findOneBy(['id' => $id])
        ;

    if (null === $media_package_file)
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return new MediaFileResponse($this->getMediaFileDataResponse($media_package_file));
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesGet(?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $qb = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from('App\Entity\MediaPackageFile', 'f')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    APIQueryHelper::addFileFlavorsCondition($qb, $flavor, 'f');
    $media_package_files = $qb->getQuery()->getResult();

    if (null === $media_package_files)
    {
      return [];
    }

    return $this->getMediaFilesDataResponse($media_package_files);
  }

  private function getMediaFilesDataResponse(array $media_package_files): array
  {
    $media_files_data_response = [];

    /** @var MediaPackageFile $media_package_file */
    foreach ($media_package_files as $media_package_file)
    {
      $media_files_data_response[] = new MediaFileResponse($this->getMediaFileDataResponse($media_package_file));
    }

    return $media_files_data_response;
  }

  private function getMediaFileDataResponse(MediaPackageFile $media_package_file, ?MediaPackage $package = null): array
  {
    if (null === $package)
    {
      $package = $media_package_file->getCategory()->getPackage()->first();
    }

    return $mediaFile = [
      'id' => $media_package_file->getId(),
      'name' => $media_package_file->getName(),
      'flavor' => $media_package_file->getFlavor(),
      'flavors' => $media_package_file->getFlavorNames(),
      'package' => $package->getName(),
      'category' => $media_package_file->getCategory()->getName(),
      'author' => $media_package_file->getAuthor(),
      'extension' => $media_package_file->getExtension(),
      'download_url' => $this->url_generator->generate(
          'download_media',
          ['id' => $media_package_file->getId()],
          UrlGenerator::ABSOLUTE_URL),
    ];
  }
}
