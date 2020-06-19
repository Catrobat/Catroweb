<?php

namespace App\Api;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Utils\APIQueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFile;
use OpenAPI\Server\Model\MediaFiles;
use OpenAPI\Server\Model\Package;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  private EntityManagerInterface $entity_manager;

  private UrlGeneratorInterface $url_generator;

  private MediaPackageFileRepository $mediapackage_file_repository;
  private RequestStack $stack;

  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator,
                              MediaPackageFileRepository $mediapackage_file_repository, RequestStack $stack)
  {
    $this->entity_manager = $entity_manager;
    $this->url_generator = $url_generator;
    $this->mediapackage_file_repository = $mediapackage_file_repository;
    $this->stack = $stack;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesSearchGet(string $query_string, ?string $flavor = null, ?int $limit = 20, ?int $offset = 0, ?string $package_name = null, &$responseCode, array &$responseHeaders)
  {
    $json_response_array = [];
    $responseCode = Response::HTTP_OK; // 200 => OK

    $found_media_files = $this->mediapackage_file_repository->search($query_string, $flavor, $package_name, $limit, $offset);

    /** @var MediaPackageFile $found_media_file */
    foreach ($found_media_files as $found_media_file)
    {
      $json_media_file = new MediaFile($this->getMediaFileResponseData($found_media_file));
      $json_response_array[] = $json_media_file;
    }

    return $json_response_array;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackagePackageNameGet(string $package_name, ?int $limit = 20, ?int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    if (null === $limit)
    {
      $limit = 20;
    }
    if (null === $offset)
    {
      $offset = 0;
    }
    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package_name])
    ;

    if (null === $media_package)
    {
      $responseCode = Response::HTTP_NOT_FOUND; // => Not found

      return null;
    }

    $total_results = 0;

    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if (empty($media_package_categories))
    {
      $repsonseData = new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);

      return $repsonseData;
    }

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category)
    {
      $media_package_files = $media_package_category->getFiles();
      $total_results += count($media_package_files);
      if ((0 != $offset && count($media_package_files) <= $offset) || count($json_response_array) === $limit)
      {
        if (0 != $offset)
        {
          $offset -= count($media_package_files);
        }
        continue;
      }
      if (null !== $media_package_files)
      {
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
          $json_response_array[] = new MediaFile($this->getMediaFileResponseData($media_package_file, $media_package));
        }
      }
    }

    $repsonseData = new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);

    return $repsonseData;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFileIdGet(int $id, &$responseCode, array &$responseHeaders)
  {
    $json_response_array = [];
    $media_package_file = $this->entity_manager->getRepository(MediaPackageFile::class)
      ->findOneBy(['id' => $id])
        ;

    if (null !== $media_package_file)
    {
      $responseCode = Response::HTTP_OK; // 200 => OK
      $json_response_array[] = new MediaFile($this->getMediaFileResponseData($media_package_file));
    }
    else
    {
      $responseCode = Response::HTTP_NOT_FOUND; // => Not found

      return null;
    }
    $responseData = new MediaFiles(['media_files' => $json_response_array]);

    return $responseData;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesGet(?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if (null === $limit)
    {
      $limit = 20;
    }
    if (null === $offset)
    {
      $offset = 0;
    }

    $qb = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from('App\Entity\MediaPackageFile', 'f')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    APIQueryHelper::addFileFlavorsCondition($qb, $flavor, 'f');
    $media_package_files = $qb->getQuery()->getResult();

    $count_qb = $this->entity_manager->createQueryBuilder()
      ->select('COUNT(f.id)')
      ->from('App\Entity\MediaPackageFile', 'f')
    ;
    APIQueryHelper::addFileFlavorsCondition($count_qb, $flavor, 'f');
    $total_results = $count_qb->getQuery()->getSingleScalarResult();

    $json_response_array = [];
    if (null !== $media_package_files)
    {
      /** @var MediaPackageFile $media_package_file */
      foreach ($media_package_files as $media_package_file)
      {
        $json_response_array[] = new MediaFile($this->getMediaFileResponseData($media_package_file));
      }
    }

    return new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);
  }

  public function getMediaFileResponseData(MediaPackageFile $media_package_file, ?MediaPackage $package = null): array
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
