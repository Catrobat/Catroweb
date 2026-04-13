<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\User\UserAvatarService;
use OpenAPI\Server\Model\FollowerResponse;
use OpenAPI\Server\Model\FollowersListResponse;

class FollowersResponseManager extends AbstractResponseManager
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly UserAvatarService $user_avatar_service,
  ) {
  }

  /**
   * @param User[] $users
   */
  public function createFollowersListResponse(
    array $users,
    int $total_followers,
    int $total_following,
    ?User $authenticated_user,
  ): FollowersListResponse {
    $data = array_map(
      fn (User $user): FollowerResponse => $this->createFollowerResponse($user, $authenticated_user),
      $users,
    );

    return new FollowersListResponse([
      'data' => array_values($data),
      'total_followers' => $total_followers,
      'total_following' => $total_following,
    ]);
  }

  private function createFollowerResponse(User $user, ?User $authenticated_user): FollowerResponse
  {
    $is_following = false;
    $follows_you = false;

    if ($authenticated_user instanceof User) {
      $is_following = $authenticated_user->getFollowing()->contains($user);
      $follows_you = $user->getFollowing()->contains($authenticated_user);
    }

    return new FollowerResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'avatar' => $this->user_avatar_service->getVariants($user),
      'project_count' => $this->project_manager->countPublicUserProjects($user->getId()),
      'follower_count' => $user->getFollowers()->count(),
      'is_following' => $is_following,
      'follows_you' => $follows_you,
    ]);
  }
}
