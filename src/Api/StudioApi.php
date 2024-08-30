<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Studio\StudioApiFacade;
use OpenAPI\Server\Api\StudioApiInterface;
use OpenAPI\Server\Model\CreateStudioErrorResponse;
use OpenAPI\Server\Model\UpdateStudioErrorResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class StudioApi extends AbstractApiController implements StudioApiInterface
{
  public function __construct(private readonly StudioApiFacade $facade)
  {
  }

  public function studioIdPost(string $id, string $accept_language, ?string $name, ?string $description, ?bool $is_public, ?bool $enable_comments, ?UploadedFile $image_file, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadStudioByID($id);
    if (null === $studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (null === $studioUser || !$studioUser->isAdmin()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdate(
      $studio,
      $name,
      $description,
      $image_file,
      $accept_language
    );
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UpdateStudioErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $studio = $this->facade->getProcessor()->update(
      $studio,
      $name,
      $description,
      $is_public,
      $enable_comments,
      $image_file
    );

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createStudioResponse($studio);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $this->facade->getResponseManager()->addStudioLocationToHeaders($responseHeaders, $studio);

    return $response;
  }

  public function studioPost(string $accept_language, ?string $name, ?string $description, bool $is_public, bool $enable_comments, ?UploadedFile $image_file, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $validation_wrapper = $this->facade->getRequestValidator()->validateCreate(
      $name,
      $description,
      $image_file,
      $accept_language
    );

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new CreateStudioErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studio = $this->facade->getProcessor()->create(
      $user,
      $name,
      $description,
      $is_public,
      $enable_comments,
      $image_file
    );

    $responseCode = Response::HTTP_CREATED;
    $response = $this->facade->getResponseManager()->createStudioResponse($studio);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $this->facade->getResponseManager()->addStudioLocationToHeaders($responseHeaders, $studio);

    return $response;
  }

  public function studioIdDelete(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $studio = $this->facade->getLoader()->loadStudioByID($id);
    if (null === $studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (null === $studioUser || !$studioUser->isAdmin()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $this->facade->getProcessor()->deleteStudio($studio, $user);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  public function studioIdGet(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadStudioByID($id);
    if (null === $studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studio->isIsPublic() && null === $studioUser) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createStudioResponse($studio);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);
    $this->facade->getResponseManager()->addStudioLocationToHeaders($responseHeaders, $studio);

    return $response;
  }
}
