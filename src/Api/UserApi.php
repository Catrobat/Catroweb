<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\User\UserApiFacade;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use App\Security\Captcha\CaptchaVerifier;
use App\User\ResetPassword\PasswordResetRequestedEvent;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
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

class UserApi extends AbstractApiController implements UserApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly UserApiFacade $facade,
    private readonly RateLimiterFactory $registrationBurstLimiter,
    private readonly RateLimiterFactory $passwordResetBurstLimiter,
    private readonly RateLimiterFactory $dataExportDailyLimiter,
    private readonly RequestStack $request_stack,
    private readonly CaptchaVerifier $captchaVerifier,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  #[\Override]
  public function userDataExportGet(int &$responseCode, array &$responseHeaders): array|object|null
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
  public function userPost(RegisterRequest $register_request, string $accept_language, int &$responseCode, array &$responseHeaders): JWTResponse|RegisterErrorResponse|null
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->registrationBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateRegistration($register_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new RegisterErrorResponse($validation_wrapper->getErrors());
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
  public function userDelete(int &$responseCode, array &$responseHeaders): void
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    $this->facade->getProcessor()->deleteUser($this->facade->getAuthenticationManager()->getAuthenticatedUser());
    $this->facade->getResponseManager()->addClearedAuthenticationCookiesToHeader($responseHeaders);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  public function userGet(&$responseCode, array &$responseHeaders): ExtendedUserDataResponse
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
  public function userIdGet(string $id, int &$responseCode, array &$responseHeaders): ?BasicUserDataResponse
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
    $response = $this->facade->getResponseManager()->createBasicUserDataResponse($user, 'ALL');
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function userPut(UpdateUserRequest $update_user_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($user, $update_user_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UpdateUserErrorResponse($validation_wrapper->getErrors());
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
  public function usersSearchGet(string $query, int $limit, int $offset, ?string $cursor, string $attributes, int &$responseCode, array &$responseHeaders): array
  {
    $users = $this->facade->getLoader()->searchUsers($query, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersDataResponse($users, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function userResetPasswordPost(ResetPasswordRequest $reset_password_request, string $accept_language, int &$responseCode, array &$responseHeaders): ?RegisterErrorResponse
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->passwordResetBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $validation_wrapper = $this->facade->getRequestValidator()->validateResetPasswordRequest($reset_password_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new RegisterErrorResponse($validation_wrapper->getErrors());
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
  public function usersGet(string $query, int $limit, int $offset, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $users = $this->facade->getLoader()->getAllUsers($query, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersDataResponse($users, 'ALL');
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

    $comments = [];
    foreach ($user->getComments() as $comment) {
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
}
