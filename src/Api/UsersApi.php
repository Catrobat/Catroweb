<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Studio\StudioApiLoader;
use App\Api\Services\Studio\StudioResponseManager;
use App\Api\Services\User\UserApiFacade;
use App\Api\Traits\CursorPaginationTrait;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Security\Captcha\CaptchaVerifier;
use App\User\ResetPassword\PasswordResetRequestedEvent;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\UsersApiInterface;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\StudioListResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use OpenAPI\Server\Model\UserDataExportResponse;
use OpenAPI\Server\Model\UserDataExportResponseCommentsInner;
use OpenAPI\Server\Model\UserDataExportResponseFollowersInner;
use OpenAPI\Server\Model\UserDataExportResponseProfile;
use OpenAPI\Server\Model\UserDataExportResponseProjectsInner;
use OpenAPI\Server\Model\UserDataExportResponseReactionsInner;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class UsersApi extends AbstractApiController implements UsersApiInterface
{
  use CursorPaginationTrait;
  use RateLimitTrait;

  private const int MAX_LIMIT = 50;

  public function __construct(
    private readonly UserApiFacade $facade,
    private readonly RateLimiterFactory $registrationBurstLimiter,
    private readonly RateLimiterFactory $passwordResetBurstLimiter,
    private readonly RateLimiterFactory $dataExportDailyLimiter,
    private readonly RequestStack $request_stack,
    private readonly CaptchaVerifier $captchaVerifier,
    private readonly EntityManagerInterface $entity_manager,
    private readonly StudioApiLoader $studio_api_loader,
    private readonly StudioResponseManager $studio_response_manager,
  ) {
  }

  #[\Override]
  public function usersMeDataExportGet(int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (null === $this->checkUserRateLimit($user, $this->dataExportDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->buildDataExportResponse($user);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  public function usersPost(RegisterRequest $register_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->registrationBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateRegistration($register_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = ApiErrorResponse::createValidationModel($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if (true === $register_request->isDryRun()) {
      $responseCode = Response::HTTP_NO_CONTENT;

      return null;
    }

    $captchaResult = $this->captchaVerifier->verify($register_request->getCaptchaToken(), $ip);
    $responseHeaders['X-Captcha-Result'] = $captchaResult['result'];
    if (!$captchaResult['success']) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $user = $this->facade->getProcessor()->registerUser($register_request);

    $responseCode = Response::HTTP_CREATED;
    $token = $this->facade->getAuthenticationManager()->createAuthenticationTokenFromUser($user);
    $refresh_token = $this->facade->getAuthenticationManager()->createRefreshTokenByUser($user);
    $response = $this->facade->getResponseManager()->createUserRegisteredResponse($token, $refresh_token);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function usersMeDelete(int &$responseCode, array &$responseHeaders): void
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    $this->facade->getProcessor()->deleteUser($this->facade->getAuthenticationManager()->getAuthenticatedUser());
    $this->facade->getResponseManager()->addClearedAuthenticationCookiesToHeader($responseHeaders);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  public function usersMeGet(int &$responseCode, array &$responseHeaders): array|object|null
  {
    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createExtendedUserDataResponse(
      $this->facade->getAuthenticationManager()->getAuthenticatedUser()
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function usersIdGet(string $id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getLoader()->findUserByID($id);

    if (!$user instanceof User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $viewer = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$this->facade->getLoader()->canAccessProfile($user, $viewer)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUserProfileResponse($user, $viewer);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function userIdStudiosGet(string $id, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getLoader()->findUserByID($id);

    if (!$user instanceof User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $viewer = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$this->facade->getLoader()->canAccessProfile($user, $viewer)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = min(max($limit, 1), self::MAX_LIMIT);
    $cursor_id = $this->decodeIdCursor($cursor);

    if (null === $cursor_id && null !== $cursor && '' !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $is_own_profile = $viewer instanceof User && $viewer->getId() === $user->getId();
    $page = $this->studio_api_loader->loadUserStudiosPage($user, $limit, $cursor_id, $is_own_profile);

    $studio_responses = [];
    foreach ($page['studios'] as $studio) {
      $studio_responses[] = $this->studio_response_manager->createStudioResponseWithUserContext($studio, $viewer);
    }

    $next_cursor = null;
    if ($page['has_more'] && [] !== $page['studios']) {
      $last_studio = end($page['studios']);
      $su_id = $this->studio_api_loader->getStudioUserIdForCursor($user, $last_studio);
      if (null !== $su_id) {
        $next_cursor = base64_encode((string) $su_id);
      }
    }

    $responseCode = Response::HTTP_OK;

    $response = new StudioListResponse();
    $response->setData($studio_responses);
    $response->setTotal($page['total']);
    $response->setHasMore($page['has_more']);
    $response->setNextCursor($next_cursor);

    return $response;
  }

  #[\Override]
  public function usersMePatch(UpdateUserRequest $update_user_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($user, $update_user_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = ApiErrorResponse::createValidationModel($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $responseCode = Response::HTTP_NO_CONTENT;

    if (true !== $update_user_request->isDryRun()) {
      $this->facade->getProcessor()->updateUser(
        $user, $update_user_request
      );

      if (!is_null($update_user_request->getUsername())) {
        $token = $this->facade->getAuthenticationManager()->createAuthenticationTokenFromUser($user);
        $refresh_token = $this->facade->getAuthenticationManager()->createRefreshTokenByUser($user);
        $this->facade->getResponseManager()->addAuthenticationCookiesToHeader($token, $refresh_token, $responseHeaders);
      }
    }

    return null;
  }

  #[\Override]
  public function usersSearchGet(string $query, int $limit, ?string $cursor, string $attributes, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $limit = min(max($limit, 1), self::MAX_LIMIT);
    $offset = $this->decodeCursorToOffset($cursor);

    $users = $this->facade->getLoader()->searchUsers($query, $limit + 1, $offset);

    $has_more = count($users) > $limit;
    if ($has_more) {
      array_pop($users);
    }

    $next_cursor = $has_more ? base64_encode((string) ($offset + $limit)) : null;

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersListResponse($users, $has_more, $next_cursor, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function usersResetPasswordPost(ResetPasswordRequest $reset_password_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->passwordResetBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateResetPasswordRequest($reset_password_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = ApiErrorResponse::createValidationModel($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $captchaResult = $this->captchaVerifier->verify($reset_password_request->getCaptchaToken(), $ip);
    $responseHeaders['X-Captcha-Result'] = $captchaResult['result'];
    if (!$captchaResult['success']) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    // Do not reveal whether a user account was found or not.
    $this->facade->getEventDispatcher()->dispatch(new PasswordResetRequestedEvent($reset_password_request->getEmail(), $accept_language));
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }

  #[\Override]
  public function usersGet(?string $query, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $limit = min(max($limit, 1), self::MAX_LIMIT);
    $offset = $this->decodeCursorToOffset($cursor);

    $users = $this->facade->getLoader()->getAllUsers($query, $limit + 1, $offset);

    $has_more = count($users) > $limit;
    if ($has_more) {
      array_pop($users);
    }

    $next_cursor = $has_more ? base64_encode((string) ($offset + $limit)) : null;

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersListResponse($users, $has_more, $next_cursor, 'ALL');
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  private function buildDataExportResponse(User $user): UserDataExportResponse
  {
    $profile = new UserDataExportResponseProfile([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'email' => $user->getEmail(),
      'about' => $user->getAbout(),
      'currently_working_on' => $user->getCurrentlyWorkingOn(),
      'created_at' => $this->toDateTime($user->getCreatedAt()),
    ]);

    $projects = [];
    foreach ($user->getPrograms() as $program) {
      $projects[] = new UserDataExportResponseProjectsInner([
        'id' => $program->getId(),
        'name' => $program->getName(),
        'description' => $program->getDescription(),
        'uploaded_at' => $program->getUploadedAt(),
        'views' => $program->getViews(),
        'downloads' => $program->getDownloads(),
        'private' => $program->getPrivate(),
      ]);
    }

    /** @var UserComment[] $userComments */
    $userComments = $this->entity_manager->createQueryBuilder()
      ->select('c')
      ->from(UserComment::class, 'c')
      ->where('c.user = :userId')
      ->setParameter('userId', $user->getId())
      ->getQuery()
      ->getResult()
    ;

    $comments = [];
    foreach ($userComments as $comment) {
      $comments[] = new UserDataExportResponseCommentsInner([
        'id' => $comment->getId(),
        'text' => $comment->getText(),
        'posted_at' => $comment->getUploadDate(),
        'parent_id' => 0 === $comment->getParentId() ? null : $comment->getParentId(),
      ]);
    }

    $reactions = [];
    foreach ($user->getLikes() as $like) {
      $reactions[] = new UserDataExportResponseReactionsInner([
        'project_id' => $like->getProgramId(),
        'type' => ProgramLike::$TYPE_NAMES[$like->getType()] ?? 'unknown',
        'created_at' => $like->getCreatedAt(),
      ]);
    }

    $followers = $this->loadRelatedUsers($user, 'f.following');
    $following = $this->loadRelatedUsers($user, 'f.followers');

    return new UserDataExportResponse([
      'exported_at' => new \DateTime(),
      'profile' => $profile,
      'projects' => $projects,
      'comments' => $comments,
      'reactions' => $reactions,
      'followers' => $followers,
      'following' => $following,
    ]);
  }

  /**
   * @return UserDataExportResponseFollowersInner[]
   */
  private function loadRelatedUsers(User $user, string $joinRelation): array
  {
    /** @var User[] $users */
    $users = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from(User::class, 'f')
      ->join($joinRelation, 'u')
      ->where('u.id = :userId')
      ->setParameter('userId', $user->getId())
      ->getQuery()
      ->getResult()
    ;

    return array_map(
      static fn (User $f): UserDataExportResponseFollowersInner => new UserDataExportResponseFollowersInner([
        'id' => $f->getId(),
        'username' => $f->getUsername(),
      ]),
      $users,
    );
  }

  private function toDateTime(?\DateTimeInterface $dateTime): ?\DateTime
  {
    if (null === $dateTime) {
      return null;
    }

    if ($dateTime instanceof \DateTime) {
      return $dateTime;
    }

    return \DateTime::createFromInterface($dateTime);
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
