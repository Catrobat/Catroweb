<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\DB\Entity\MediaLibrary\MediaPackage;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use Symfony\Component\HttpFoundation\Response;

final class MediaLibraryApi extends AbstractApiController implements MediaLibraryApiInterface
{
  public function __construct(private readonly MediaLibraryApiFacade $facade)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesSearchGet(string $query, ?string $flavor = null, ?int $limit = 20, ?int $offset = 0, ?string $package_name = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);
    $package_name ??= '';

    $found_media_files = $this->facade->getLoader()->searchMediaLibraryFiles($query, $flavor, $package_name, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFilesDataResponse($found_media_files);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackageNameGet(string $name, ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null): ?array
  {
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);

    $media_package = $this->facade->getLoader()->getMediaPackageByName($name);

    if (is_null($media_package)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    /** @var MediaPackage $media_package */
    $response = $this->facade->getResponseManager()->createMediaPackageCategoriesResponse(
      $media_package->getCategories()->toArray(), $limit, $offset
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFileIdGet(int $id, &$responseCode = null, array &$responseHeaders = null): ?\OpenAPI\Server\Model\MediaFileResponse
  {
    $media_package_file = $this->facade->getLoader()->getMediaPackageFileByID($id);

    if (is_null($media_package_file)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFileResponse($media_package_file);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesGet(?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);
    $flavor = $this->getDefaultFlavorOnNull($flavor);

    $media_package_files = $this->facade->getLoader()->getMediaPackageFiles($limit, $offset, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createMediaFilesDataResponse($media_package_files);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }
}
