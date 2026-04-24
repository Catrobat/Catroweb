<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\User\User;
use App\User\UserAvatarService;
use OpenAPI\Server\Model\FollowerResponse;
use OpenAPI\Server\Model\FollowersListResponse;

class FollowersResponseManager extends AbstractResponseManager
{
  public function __construct(
    private readonly UserAvatarService $user_avatar_service,
  ) {
  }

  /**
   * @param User[]              $users
   * @param array<string, true> $following_ids   user IDs the authenticated user follows
   * @param array<string, true> $follows_you_ids user IDs that follow the authenticated user
   * @param array<string, int>  $follower_counts user_id => follower count
   * @param array<string, int>  $project_counts  user_id => public project count
   */
  public function createFollowersListResponse(
    array $users,
    int $total_followers,
    int $total_following,
    array $following_ids = [],
    array $follows_you_ids = [],
    array $follower_counts = [],
    array $project_counts = [],
  ): FollowersListResponse {
    $data = array_map(
      fn (User $user): FollowerResponse => $this->createFollowerResponse(
        $user, $following_ids, $follows_you_ids, $follower_counts, $project_counts,
      ),
      $users,
    );

    return new FollowersListResponse([
      'data' => array_values($data),
      'total_followers' => $total_followers,
      'total_following' => $total_following,
    ]);
  }

  /**
   * @param array<string, true> $following_ids
   * @param array<string, true> $follows_you_ids
   * @param array<string, int>  $follower_counts
   * @param array<string, int>  $project_counts
   */
  private function createFollowerResponse(
    User $user,
    array $following_ids,
    array $follows_you_ids,
    array $follower_counts,
    array $project_counts,
  ): FollowerResponse {
    $user_id = (string) $user->getId();

    return new FollowerResponse([
      'id' => $user_id,
      'username' => $user->getUsername(),
      'avatar' => $this->user_avatar_service->getVariants($user),
      'project_count' => $project_counts[$user_id] ?? 0,
      'follower_count' => $follower_counts[$user_id] ?? 0,
      'is_following' => isset($following_ids[$user_id]),
      'follows_you' => isset($follows_you_ids[$user_id]),
    ]);
  }
}
