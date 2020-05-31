<?php

namespace App\Api;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFile;
use OpenAPI\Server\Model\MediaFiles;
use OpenAPI\Server\Model\Package;
use Symfony\Component\HttpFoundation\RequestStack;
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
  public function mediaFileSearchGet(string $query_string, ?string $flavor = null, ?int $limit = 20, ?int $offset = 0, ?string $package_name = null, &$responseCode, array &$responseHeaders)
  {
    $json_response_array = [];
    $responseCode = 200; // 200 => OK

    $found_media_files = $this->mediapackage_file_repository->search($query_string, $flavor, $package_name, $limit, $offset);

    /** @var MediaPackageFile $found_media_file */
    foreach ($found_media_files as $found_media_file)
    {
      $json_media_file = new MediaFile();
      $json_media_file->setId($found_media_file->getId());
      $json_media_file->setName($found_media_file->getName());
      $json_media_file->setPackage($found_media_file->getCategory()->getPackage()->first());
      $json_media_file->setCategory($found_media_file->getCategory()->getName());
      $json_media_file->setAuthor($found_media_file->getAuthor());
      $json_media_file->setExtension($found_media_file->getExtension());
      $json_media_file->setFlavor($found_media_file->getFlavor());
      $download_url = $this->url_generator->generate(
          'download_media',
          [
            'id' => $found_media_file->getId(),
          ],
          UrlGenerator::ABSOLUTE_URL);
      $json_media_file->setDownloadUrl($download_url);

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
      $responseCode = 404; // => Not found
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
          $mediaFile = [
            'id' => $media_package_file->getId(),
            'name' => $media_package_file->getName(),
            'flavor' => $media_package_file->getFlavor(),
            'package' => $media_package->getName(),
            'category' => $media_package_file->getCategory()->getName(),
            'author' => $media_package_file->getAuthor(),
            'extension' => $media_package_file->getExtension(),
            'download_url' => $this->url_generator->generate(
              'download_media',
              ['id' => $media_package_file->getId()],
              UrlGenerator::ABSOLUTE_URL),
          ];

          $json_response_array[] = new MediaFile($mediaFile);
        }
      }
    }

    $repsonseData = new MediaFiles(['media_files' => $json_response_array, 'total_results' => $total_results]);

    return $repsonseData;
  }
}
