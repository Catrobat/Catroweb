<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFileResponse;
use Symfony\Component\HttpFoundation\Response;

class MediaLibraryApi extends AbstractApiController implements MediaLibraryApiInterface
{
  public function __construct(private readonly MediaLibraryApiFacade $facade)
  {
  }

  public function mediaFilesSearchGet(string $query, int $limit, int $offset, string $attributes, string $flavor, string $package_name, int &$responseCode, array &$responseHeaders): array
  {
    $found_media_files = $this->facade->getLoader()->searchMediaLibraryFiles($query, $flavor, $package_name, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFilesDataResponse($found_media_files, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function mediaPackageNameGet(string $name, int $limit, int $offset, string $attributes, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $media_package = $this->facade->getLoader()->getMediaPackageByName($name);

    if (is_null($media_package)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaPackageCategoriesResponse(
      $media_package->getCategories()->toArray(), $limit, $offset, $attributes
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function mediaFileIdGet(int $id, string $attributes, int &$responseCode, array &$responseHeaders): ?MediaFileResponse
  {
    $media_package_file = $this->facade->getLoader()->getMediaPackageFileByID($id);

    if (is_null($media_package_file)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    // TODO: create new API version and remove unused attributes parameter

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFileResponse($media_package_file, 'ALL');
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function mediaFilesGet(int $limit, int $offset, string $attributes, string $flavor, int &$responseCode, array &$responseHeaders): array
  {
    $media_package_files = $this->facade->getLoader()->getMediaPackageFiles($limit, $offset, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFilesDataResponse($media_package_files, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }
}
