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
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;

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
  public function usersIdFollowersGet(string $id, int &$responseCode, array &$responseHeaders): ?FollowersListResponse
  {
    return $this->handleGetList($id, $responseCode, fn (User $user): array => $this->facade->getLoader()->getFollowers($user));
  }

  #[\Override]
  public function usersIdFollowingGet(string $id, int &$responseCode, array &$responseHeaders): ?FollowersListResponse
  {
    return $this->handleGetList($id, $responseCode, fn (User $user): array => $this->facade->getLoader()->getFollowing($user));
  }

  /**
   * @param callable(User): array{users: User[], total_followers: int, total_following: int} $load_data
   */
  private function handleGetList(string $id, int &$responseCode, callable $load_data): ?FollowersListResponse
  {
    $user = $this->user_manager->find($id);
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $authenticated_user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();

    if (!$this->user_api_loader->canAccessProfile($user, $authenticated_user)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }
    $page_data = $load_data($user);
    $users = $page_data['users'];

    $user_ids = array_values(array_filter(
      array_map(static fn (User $u): ?string => $u->getId(), $users),
    ));

    $loader = $this->facade->getLoader();
    $following_ids = [];
    $follows_you_ids = [];
    if ($authenticated_user instanceof User) {
      $following_ids = $loader->getFollowedUserIds($authenticated_user, $user_ids);
      $follows_you_ids = $loader->getFollowerOfUserIds($authenticated_user, $user_ids);
    }

    $follower_counts = $loader->getFollowerCountsForUsers($user_ids);
    $project_counts = $loader->getProjectCountsForUsers($user_ids);

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createFollowersListResponse(
      $users,
      $page_data['total_followers'],
      $page_data['total_following'],
      $following_ids,
      $follows_you_ids,
      $follower_counts,
      $project_counts,
    );
  }

  #[\Override]
  public function usersIdFollowPost(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (null === $this->checkUserRateLimit($user, $this->followBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    if ($user->getId() === $id) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $user_to_follow = $this->user_manager->find($id);
    if (!$user_to_follow instanceof User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if ($user->getFollowing()->contains($user_to_follow)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    try {
      $this->facade->getProcessor()->followUser($user, $user_to_follow);
    } catch (\Exception) {
      // Race condition: concurrent request already created the follow
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $responseCode = Response::HTTP_OK;
  }

  #[\Override]
  public function usersIdUnfollowDelete(string $id, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (null === $this->checkUserRateLimit($user, $this->followBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    if ($user->getId() === $id) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return;
    }

    $user_to_unfollow = $this->user_manager->find($id);
    if (!$user_to_unfollow instanceof User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $this->facade->getProcessor()->unfollowUser($user, $user_to_unfollow);

    $responseCode = Response::HTTP_NO_CONTENT;
  }
}
