<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Followers\FollowersApiFacade;
use App\Api\Services\User\UserApiLoader;
use App\DB\Entity\User\User;
use App\User\UserManager;
use OpenAPI\Server\Api\FollowersApiInterface;
use OpenAPI\Server\Model\FollowersListResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class FollowersApi extends AbstractApiController implements FollowersApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly FollowersApiFacade $facade,
    private readonly UserManager $user_manager,
    private readonly UserApiLoader $user_api_loader,
    private readonly RateLimiterFactory $followBurstLimiter,
  ) {
  }

  #[\Override]
  public function userIdFollowersGet(string $id, int &$responseCode, array &$responseHeaders): ?FollowersListResponse
  {
    return $this->handleGetList($id, $responseCode, fn ($user) => $this->facade->getLoader()->getFollowers($user));
  }

  #[\Override]
  public function userIdFollowingGet(string $id, int &$responseCode, array &$responseHeaders): ?FollowersListResponse
  {
    return $this->handleGetList($id, $responseCode, fn ($user) => $this->facade->getLoader()->getFollowing($user));
  }

  /**
   * @param callable(User): array{users: User[], total_followers: int, total_following: int} $load_data
   */
  private function handleGetList(string $id, int &$responseCode, callable $load_data): ?FollowersListResponse
  {
    $user = $this->user_manager->find($id);
    if (null === $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $authenticated_user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if (!$this->user_api_loader->canAccessProfile($user, $authenticated_user)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }
    $page_data = $load_data($user);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createFollowersListResponse(
      $page_data['users'],
      $page_data['total_followers'],
      $page_data['total_following'],
      $authenticated_user,
    );
  }

  #[\Override]
  public function userIdFollowPost(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (!$this->checkUserRateLimit($user, $this->followBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    if ($user->getId() === $id) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $user_to_follow = $this->user_manager->find($id);
    if (null === $user_to_follow) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if ($user->getFollowing()->contains($user_to_follow)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $this->facade->getProcessor()->followUser($user, $user_to_follow);

    $responseCode = Response::HTTP_OK;
  }

  #[\Override]
  public function userIdUnfollowDelete(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (!$this->checkUserRateLimit($user, $this->followBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    if ($user->getId() === $id) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $user_to_unfollow = $this->user_manager->find($id);
    if (null === $user_to_unfollow) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $this->facade->getProcessor()->unfollowUser($user, $user_to_unfollow);

    $responseCode = Response::HTTP_NO_CONTENT;
  }
}
