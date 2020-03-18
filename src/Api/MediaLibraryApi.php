<?php

namespace App\Api;

use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\Package;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApi implements MediaLibraryApiInterface
{
  /** var EntityManagerInterface */
  private $entity_manager;

  /** var UrlGeneratorInterface */
  private $url_generator;

  /**
   * MediaLibraryApi constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator)
  {
    $this->entity_manager = $entity_manager;
    $this->url_generator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackagePackageNameGet(string $packageName, &$responseCode, array &$responseHeaders)
  {
    $media_package = $this->entity_manager->getRepository('App\Entity\MediaPackage')->findOneBy(['nameUrl' => $packageName]);
    if (null === $media_package)
    {
      $responseCode = 404; // 404 => Not found
    }
    else
    {
      $json_response_array = [];
      /** @var array|MediaPackageCategory $media_package_categories */
      $media_package_categories = $media_package->getCategories();
      if (null === $media_package_categories || empty($media_package_categories))
      {
        return $json_response_array;
      }
      foreach ($media_package_categories as $media_package_category)
      {
        /** @var array|MediaPackageFile $media_package_files */
        $media_package_files = $media_package_category->getFiles();
        if (null != $media_package_files)
        {
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

            $download_url = $this->url_generator->generate('download_media', ['id' => $media_package_file->getId()], UrlGenerator::ABSOLUTE_URL);
            $json_media_package->setDownloadUrl($download_url);

            array_push($json_response_array, $json_media_package);
          }
        }
      }

      return $json_response_array;
    }
  }
}
