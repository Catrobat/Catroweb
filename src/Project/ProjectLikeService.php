<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectLike;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectLikeRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class ProjectLikeService
{
  public function __construct(
    private readonly ProjectLikeRepository $project_like_repository,
  ) {
  }

  /**
   * @return ProjectLike[]
   */
  public function findUserLikes(string $project_id, string $user_id): array
  {
    return $this->project_like_repository->findBy(['program_id' => $project_id, 'user_id' => $user_id]);
  }

  /**
   * @throws NoResultException
   */
  public function areThereOtherLikeTypes(Project $project, User $user, int $type): bool
  {
    try {
      return $this->project_like_repository->areThereOtherLikeTypes($project, $user, $type);
    } catch (NonUniqueResultException) {
      return false;
    }
  }

  public function likeTypeCount(string $project_id, int $type): int
  {
    return $this->project_like_repository->likeTypeCount($project_id, $type);
  }

  public function totalLikeCount(string $project_id): int
  {
    return $this->project_like_repository->totalLikeCount($project_id);
  }

  /**
   * @return array<int, int> map of type_id => count
   */
  public function getReactionCountsByType(string $project_id): array
  {
    return $this->project_like_repository->getReactionCountsByType($project_id);
  }

  /**
   * @return array{data: array, next_cursor: ?string, has_more: bool}
   */
  public function getReactionUsersPaginated(string $project_id, ?int $type, int $limit, ?string $cursor): array
  {
    return $this->project_like_repository->getReactionUsersPaginated($project_id, $type, $limit, $cursor);
  }
}
