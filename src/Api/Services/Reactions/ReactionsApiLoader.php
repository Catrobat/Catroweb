<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use App\Project\ProjectManager;

class ReactionsApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ProgramLikeRepository $program_like_repository,
  ) {
  }

  public function findProjectIfVisibleToCurrentUser(string $id, ?User $user): ?Program
  {
    $project = $this->project_manager->find($id);

    if (null === $project) {
      return null;
    }

    // Project is visible if it's public OR if the user owns it
    if ($project->isVisible() && !$project->getPrivate()) {
      return $project;
    }

    // Private/invisible projects are only visible to their owner
    if (null !== $user && $project->getUser() === $user) {
      return $project;
    }

    return null;
  }

  /**
   * Get reaction counts by type for a project.
   *
   * @return array{total: int, thumbs_up: int, smile: int, love: int, wow: int, active_types: string[]}
   */
  public function getReactionCounts(string $project_id): array
  {
    $counts = [
      'total' => $this->program_like_repository->totalLikeCount($project_id),
      'thumbs_up' => $this->program_like_repository->likeTypeCount($project_id, ProgramLike::TYPE_THUMBS_UP),
      'smile' => $this->program_like_repository->likeTypeCount($project_id, ProgramLike::TYPE_SMILE),
      'love' => $this->program_like_repository->likeTypeCount($project_id, ProgramLike::TYPE_LOVE),
      'wow' => $this->program_like_repository->likeTypeCount($project_id, ProgramLike::TYPE_WOW),
    ];

    $active_type_ids = $this->program_like_repository->likeTypesOfProject($project_id);
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

    $likes = $this->project_manager->findUserLikes($project_id, $user_id);

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

    $likes = $this->project_manager->findUserLikes($project_id, $user_id);

    foreach ($likes as $like) {
      if ($like->getType() === $type) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get paginated list of users who reacted to a project.
   *
   * @return array{data: array, next_cursor: ?string, has_more: bool}
   */
  public function getReactionUsersPaginated(string $project_id, ?int $type, int $limit, ?string $cursor): array
  {
    return $this->program_like_repository->getReactionUsersPaginated($project_id, $type, $limit, $cursor);
  }
}
