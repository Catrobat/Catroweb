<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use App\Project\ProjectLikeService;
use App\Project\ProjectManager;

class ReactionsApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ProjectLikeService $project_like_service,
  ) {
  }

  public function findProjectIfVisibleToCurrentUser(string $id, ?User $user): ?Program
  {
    $project = $this->project_manager->find($id);

    if (!$project instanceof Program) {
      return null;
    }

    // Admin-hidden projects are only visible to their owner
    if (!$project->isVisible()) {
      if ($user instanceof User && $project->getUser() === $user) {
        return $project;
      }

      return null;
    }

    // Visible projects (including private ones) are accessible via direct link
    return $project;
  }

  /**
   * Get reaction counts by type for a project.
   *
   * @return array{total: int, thumbs_up: int, smile: int, love: int, wow: int, active_types: string[]}
   */
  public function getReactionCounts(string $project_id): array
  {
    $counts = [
      'total' => $this->project_like_service->totalLikeCount($project_id),
      'thumbs_up' => $this->project_like_service->likeTypeCount($project_id, ProgramLike::TYPE_THUMBS_UP),
      'smile' => $this->project_like_service->likeTypeCount($project_id, ProgramLike::TYPE_SMILE),
      'love' => $this->project_like_service->likeTypeCount($project_id, ProgramLike::TYPE_LOVE),
      'wow' => $this->project_like_service->likeTypeCount($project_id, ProgramLike::TYPE_WOW),
    ];

    $active_type_ids = $this->project_like_service->findProjectLikeTypes($project_id);
    $counts['active_types'] = array_map(
      static fn (int $type_id): string => ProgramLike::$TYPE_NAMES[$type_id] ?? '',
      $active_type_ids
    );

    return $counts;
  }

  /**
   * Get the current user's reactions to a project.
   *
   * @return string[] Array of reaction type names (e.g., ['thumbs_up', 'love'])
   */
  public function getUserReactions(string $project_id, User $user): array
  {
    $user_id = $user->getId();
    if (null === $user_id) {
      return [];
    }

    $likes = $this->project_like_service->findUserLikes($project_id, $user_id);

    return array_map(
      static fn (ProgramLike $like): string => ProgramLike::$TYPE_NAMES[$like->getType()] ?? '',
      $likes
    );
  }

  /**
   * Check if a user has a specific reaction on a project.
   */
  public function hasReaction(string $project_id, User $user, int $type): bool
  {
    $user_id = $user->getId();
    if (null === $user_id) {
      return false;
    }

    $likes = $this->project_like_service->findUserLikes($project_id, $user_id);

    return array_any($likes, fn ($like): bool => $like->getType() === $type);
  }

  /**
   * Get paginated list of users who reacted to a project.
   *
   * @return array{data: array, next_cursor: ?string, has_more: bool}
   */
  public function getReactionUsersPaginated(string $project_id, ?int $type, int $limit, ?string $cursor): array
  {
    return $this->project_like_service->getReactionUsersPaginated($project_id, $type, $limit, $cursor);
  }
}
