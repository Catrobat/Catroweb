<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class ProjectLikeService
{
  public function __construct(
    private readonly ProgramLikeRepository $project_like_repository,
  ) {
  }

  /**
   * @return ProgramLike[]
   */
  public function findUserLikes(string $project_id, string $user_id): array
  {
    return $this->project_like_repository->findBy(['program_id' => $project_id, 'user_id' => $user_id]);
  }

  public function findProjectLikeTypes(string $project_id): array
  {
    return $this->project_like_repository->likeTypesOfProject($project_id);
  }

  /**
   * @throws NoResultException|\InvalidArgumentException
   */
  public function changeLike(Program $project, User $user, int $type, string $action): void
  {
    if (ProgramLike::ACTION_ADD === $action) {
      $this->project_like_repository->addLike($project, $user, $type);
    } elseif (ProgramLike::ACTION_REMOVE === $action) {
      $this->project_like_repository->removeLike($project, $user, $type);
    } else {
      throw new \InvalidArgumentException('Invalid action: '.$action);
    }
  }

  /**
   * @throws NoResultException
   */
  public function areThereOtherLikeTypes(Program $project, User $user, int $type): bool
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
   * @return array{data: array, next_cursor: ?string, has_more: bool}
   */
  public function getReactionUsersPaginated(string $project_id, ?int $type, int $limit, ?string $cursor): array
  {
    return $this->project_like_repository->getReactionUsersPaginated($project_id, $type, $limit, $cursor);
  }
}
