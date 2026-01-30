<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class ProgramLikeRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramLike::class);
  }

  public function likeTypeCount(string $program_id, int $type): int
  {
    $qb = $this->createQueryBuilder('l');

    $result = $qb
      ->select('l')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $program_id)
      ->setParameter(':type', $type)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }

  /**
   * @return int[]
   */
  public function likeTypesOfProject(string $project_id): array
  {
    $qb = $this->createQueryBuilder('l');

    $qb
      ->select('l.type')->distinct()
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $project_id)
    ;

    /** @var array<array{type: int}> $result */
    $result = $qb->getQuery()->getResult();

    return array_map(static fn (array $x): int => $x['type'], $result);
  }

  public function totalLikeCount(string $program_id): int
  {
    $qb = $this->createQueryBuilder('l');

    $result = $qb
      ->select('l')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }

  /**
   * @param string[] $user_ids
   * @param string[] $exclude_program_ids
   *
   * @return ProgramLike[]
   */
  public function getLikesOfUsers(array $user_ids, string $exclude_user_id, array $exclude_program_ids, string $flavor): array
  {
    $qb = $this->createQueryBuilder('l');

    return $qb
      ->select('l')
      ->innerJoin(Program::class, 'p', Join::WITH, $qb->expr()->eq('p.id', 'l.program')->__toString())
      ->where($qb->expr()->in('l.user_id', ':user_ids'))
      ->andWhere($qb->expr()->neq('IDENTITY(p.user)', ':exclude_user_id'))
      ->andWhere($qb->expr()->notIn('p.id', ':exclude_program_ids'))
      ->andWhere($qb->expr()->eq('p.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('p.flavor', ':flavor'))
      ->andWhere($qb->expr()->eq('p.private', $qb->expr()->literal(false)))
      ->setParameter('user_ids', $user_ids)
      ->setParameter('exclude_user_id', $exclude_user_id)
      ->setParameter('exclude_program_ids', $exclude_program_ids)
      ->setParameter('flavor', $flavor)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }

  public function addLike(Program $project, User $user, int $type): void
  {
    $entityManager = $this->getEntityManager();
    $entityManager->beginTransaction();

    try {
      $obj = new ProgramLike($project, $user, $type);
      $entityManager->persist($obj);
      $entityManager->flush();
      $entityManager->commit();
    } catch (\Exception) {
      // Like already exits, do nothing
      $entityManager->rollback();
    }
  }

  public function removeLike(Program $project, User $user, int $type): void
  {
    $qb = $this->createQueryBuilder('l');
    $qb->delete()
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type)
    ;

    $qb->getQuery()->execute();
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function areThereOtherLikeTypes(Program $project, User $user, int $type): bool
  {
    $qb = $this->createQueryBuilder('l');
    $qb->select('count(l)')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->neq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type)
    ;

    $count = $qb->getQuery()->getSingleScalarResult();

    return ctype_digit($count) && $count > 0;
  }

  /**
   * Get paginated list of users who reacted to a project using cursor-based pagination.
   *
   * @param string      $project_id The project ID
   * @param int|null    $type       Optional reaction type filter
   * @param int         $limit      Maximum number of users to return
   * @param string|null $cursor     User ID to start after (for pagination)
   *
   * @return array{data: array<array{user: User, types: int[], reacted_at: \DateTimeInterface|null}>, next_cursor: string|null, has_more: bool}
   */
  public function getReactionUsersPaginated(string $project_id, ?int $type, int $limit, ?string $cursor): array
  {
    $qb = $this->createQueryBuilder('l');

    $qb
      ->select('l, u')
      ->join('l.user', 'u')
      ->where($qb->expr()->eq('l.program_id', ':project_id'))
      ->setParameter('project_id', $project_id)
      ->orderBy('l.user_id', 'ASC')
    ;

    if (null !== $type) {
      $qb
        ->andWhere($qb->expr()->eq('l.type', ':type'))
        ->setParameter('type', $type)
      ;
    }

    if (null !== $cursor && '' !== $cursor) {
      $qb
        ->andWhere($qb->expr()->gt('l.user_id', ':cursor'))
        ->setParameter('cursor', $cursor)
      ;
    }

    // Fetch one extra to check if more exist
    $qb->setMaxResults($limit + 1);

    /** @var ProgramLike[] $results */
    $results = $qb->getQuery()->getResult();

    $has_more = count($results) > $limit;
    if ($has_more) {
      array_pop($results);
    }

    // Group reactions by user
    $users_data = [];
    foreach ($results as $like) {
      $user_id = $like->getUser()->getId();

      if (!isset($users_data[$user_id])) {
        $users_data[$user_id] = [
          'user' => $like->getUser(),
          'types' => [],
          'reacted_at' => $like->getCreatedAt(),
        ];
      }

      $users_data[$user_id]['types'][] = $like->getType();

      // Keep the most recent reaction time
      $created_at = $like->getCreatedAt();
      if (null !== $created_at && (null === $users_data[$user_id]['reacted_at'] || $created_at > $users_data[$user_id]['reacted_at'])) {
        $users_data[$user_id]['reacted_at'] = $created_at;
      }
    }

    $data = array_values($users_data);
    $next_cursor = $has_more && [] !== $data ? end($data)['user']->getId() : null;

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
  }
}
