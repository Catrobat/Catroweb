<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Studio\StudioApiFacade;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\User;
use OpenAPI\Server\Api\StudioApiInterface;
use OpenAPI\Server\Model\CreateStudioErrorResponse;
use OpenAPI\Server\Model\StudioAddProjectRequest;
use OpenAPI\Server\Model\StudioCommentCreateRequest;
use OpenAPI\Server\Model\UpdateStudioErrorResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class StudioApi extends AbstractApiController implements StudioApiInterface
{
  use RateLimitTrait;

  private const int DEFAULT_LIMIT = 20;
  private const int MAX_LIMIT = 50;

  public function __construct(
    private readonly StudioApiFacade $facade,
    private readonly RateLimiterFactory $studioCreateDailyLimiter,
  ) {
  }

  #[\Override]
  public function studioGet(string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $limit = $this->normalizeLimit($limit);
    $cursor_id = $this->decodeIdCursor($cursor);
    if (null === $cursor_id && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->facade->getLoader()->loadStudiosPage($limit, $cursor_id);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createStudioListResponse(
      $page['studios'],
      $page['has_more'],
    );
  }

  #[\Override]
  public function studioIdGet(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studio->isIsPublic() && !$studioUser instanceof StudioUser) {
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

  #[\Override]
  public function studioPost(string $accept_language, ?string $name, ?string $description, bool $is_public, bool $enable_comments, ?UploadedFile $image_file, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if ($user instanceof User && null === $this->checkUserRateLimit($user, $this->studioCreateDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

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

    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    $studio = $this->facade->getProcessor()->create(
      $user,
      $name ?? '',
      $description ?? '',
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

  #[\Override]
  public function studioIdPost(string $id, string $accept_language, ?string $name, ?string $description, ?bool $is_public, ?bool $enable_comments, ?UploadedFile $image_file, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studioUser instanceof StudioUser || !$studioUser->isAdmin()) {
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

  #[\Override]
  public function studioIdDelete(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studioUser instanceof StudioUser || !$studioUser->isAdmin()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $this->facade->getProcessor()->deleteStudio($studio, $user);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function studioIdMembersGet(string $id, string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = $this->normalizeLimit($limit);
    $cursor_id = $this->decodeIdCursor($cursor);
    if (null === $cursor_id && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->facade->getLoader()->loadMembersPage($studio, $limit, $cursor_id);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createMemberListResponse(
      $page['members'],
      $page['has_more'],
    );
  }

  #[\Override]
  public function studioIdJoinPost(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $existingMember = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if ($existingMember instanceof StudioUser) {
      $responseCode = Response::HTTP_CONFLICT;

      return;
    }

    $this->facade->getProcessor()->joinStudio($user, $studio);
    $responseCode = Response::HTTP_OK;
  }

  #[\Override]
  public function studioIdLeaveDelete(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $existingMember = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$existingMember instanceof StudioUser) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if ($existingMember->isAdmin()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $this->facade->getProcessor()->leaveStudio($user, $studio);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function studioIdProjectsGet(string $id, string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = $this->normalizeLimit($limit);
    $cursor_id = $this->decodeIdCursor($cursor);
    if (null === $cursor_id && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->facade->getLoader()->loadProjectsPage($studio, $limit, $cursor_id);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createProjectListResponse(
      $page['projects'],
      $page['has_more'],
    );
  }

  #[\Override]
  public function studioIdProjectsPost(string $id, StudioAddProjectRequest $studio_add_project_request, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studioUser instanceof StudioUser) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $project_id = $studio_add_project_request->getProjectId();
    if (null === $project_id || '' === $project_id) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $result = $this->facade->getProcessor()->addProject($user, $studio, $project_id);
    if ('not_found' === $result) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }
    if ('conflict' === $result) {
      $responseCode = Response::HTTP_CONFLICT;

      return;
    }

    $responseCode = Response::HTTP_CREATED;
  }

  #[\Override]
  public function studioIdProjectsProjectIdDelete(string $id, string $project_id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studioUser instanceof StudioUser) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $result = $this->facade->getProcessor()->removeProject($user, $studio, $project_id);
    if ('not_found' === $result) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }
    if ('forbidden' === $result) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function studioIdCommentsGet(string $id, string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = $this->normalizeLimit($limit);
    $cursor_id = $this->decodeIdCursor($cursor);
    if (null === $cursor_id && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->facade->getLoader()->loadCommentsPage($studio, $limit, $cursor_id);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createCommentListResponse(
      $page['comments'],
      $page['has_more'],
    );
  }

  #[\Override]
  public function studioIdCommentsPost(string $id, StudioCommentCreateRequest $studio_comment_create_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if ($user->isMinor()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $studio = $this->facade->getLoader()->loadVisibleStudio($id);
    if (!$studio instanceof Studio) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if (!$studio->isAllowComments()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $studioUser = $this->facade->getLoader()->loadStudioUser($user, $studio);
    if (!$studioUser instanceof StudioUser) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $message = trim((string) $studio_comment_create_request->getMessage());
    if ('' === $message) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $parent_id = $studio_comment_create_request->getParentId() ?? 0;

    $comment = $this->facade->getProcessor()->addComment($user, $studio, $message, $parent_id);
    if (null === $comment) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $responseCode = Response::HTTP_CREATED;

    return $this->facade->getResponseManager()->createCommentResponse($comment);
  }

  private function normalizeLimit(int $limit): int
  {
    $limit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

    return min($limit, self::MAX_LIMIT);
  }

  private function decodeIdCursor(?string $cursor): ?int
  {
    if (null === $cursor || '' === $cursor) {
      return null;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded || !ctype_digit($decoded)) {
      return null;
    }

    return (int) $decoded;
  }
}
