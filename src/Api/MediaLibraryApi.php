<?php

namespace App\Api;

use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\Package;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  private EntityManagerInterface $entity_manager;

  private UrlGeneratorInterface $url_generator;

  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator)
  {
    $this->entity_manager = $entity_manager;
    $this->url_generator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackagePackageNameGet(string $package_name, &$responseCode, array &$responseHeaders)
  {
    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package_name])
    ;

    if (null === $media_package)
    {
      $responseCode = 404; // => Not found
      return null;
    }

    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if (empty($media_package_categories))
    {
      return $json_response_array;
    }

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category)
    {
      $media_package_files = $media_package_category->getFiles();
      if (null !== $media_package_files)
      {
        /** @var MediaPackageFile $media_package_file */
        foreach ($media_package_files as $media_package_file)
        {
          $json_media_package = new Package();
          $json_media_package->setId($media_package_file->getId());
          $json_media_package->setName($media_package_file->getName());
          $json_media_package->setPackage($media_package->getName());
          $json_media_package->setCategory($media_package_file->getCategory()->getName());
          $json_media_package->setAuthor($media_package_file->getAuthor());
          $json_media_package->setExtension($media_package_file->getExtension());
          $json_media_package->setFlavor($media_package_file->getFlavor());

          $download_url = $this->url_generator->generate(
            'download_media',
            [
              'id' => $media_package_file->getId(),
            ],
            UrlGenerator::ABSOLUTE_URL);
          $json_media_package->setDownloadUrl($download_url);

          $json_response_array[] = $json_media_package;
        }
      }
    }

    return $json_response_array;
  }

  public function mediaFileSearchGet(string $query_string, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement mediaFileSearchGet() method.
  }
}
