<?php

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractResponseManager;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use OpenAPI\Server\Model\MediaFileResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MediaLibraryResponseManager extends AbstractResponseManager
{
  private UrlGeneratorInterface $url_generator;
  private ParameterBagInterface $parameter_bag;

  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    UrlGeneratorInterface $url_generator,
    ParameterBagInterface $parameter_bag
  ) {
    parent::__construct($translator, $serializer);
    $this->url_generator = $url_generator;
    $this->parameter_bag = $parameter_bag;
  }

  public function createMediaFilesDataResponse(array $media_package_files): array
  {
    $media_files_data_response = [];

    /** @var MediaPackageFile $media_package_file */
    foreach ($media_package_files as $media_package_file) {
      $media_files_data_response[] = $this->createMediaFileResponse($media_package_file);
    }

    return $media_files_data_response;
  }

  public function createMediaFileResponse(MediaPackageFile $media_package_file): MediaFileResponse
  {
    return new MediaFileResponse(
      [
        'id' => $media_package_file->getId(),
        'name' => $media_package_file->getName(),
        'flavors' => $media_package_file->getFlavorNames(),
        'packages' => $media_package_file->getCategory()->getPackageNames(),
        'category' => $media_package_file->getCategory()->getName(),
        'author' => $media_package_file->getAuthor(),
        'extension' => $media_package_file->getExtension(),
        'download_url' => $this->url_generator->generate(
          'download_media',
          [
            'theme' => $this->parameter_bag->get('umbrellaTheme'),
            'id' => $media_package_file->getId(),
          ],
          UrlGenerator::ABSOLUTE_URL),
      ]
    );
  }

  public function createMediaPackageCategoriesResponse(array $media_package_categories, int $limit, int $offset): array
  {
    $response_array = [];

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category) {
      $media_package_files = $media_package_category->getFiles();
      if ((0 != $offset && count($media_package_files) <= $offset) || count($response_array) === $limit) {
        if (0 != $offset) {
          $offset -= count($media_package_files);
        }
        continue;
      }

      /** @var MediaPackageFile $media_package_file */
      foreach ($media_package_files as $media_package_file) {
        if (0 != $offset) {
          --$offset;
          continue;
        }
        if (count($response_array) === $limit) {
          break;
        }
        $response_array[] = $this->createMediaFileResponse($media_package_file);
      }
    }

    return $response_array;
  }
}
