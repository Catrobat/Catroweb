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

  public function likeTypesOfProject(string $project_id): array
  {
    $qb = $this->createQueryBuilder('l');

    $qb
      ->select('l.type')->distinct()
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $project_id)
    ;

    return array_map(fn ($x): mixed => $x['type'], $qb->getQuery()->getResult());
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

  /**
   * @throws NoResultException
   */
  public function addLike(Program $project, User $user, int $type): void
  {
    if ($this->likeExists($project, $user, $type)) {
      // Like exists already, nothing to do.
      return;
    }

    $obj = new ProgramLike($project, $user, $type);
    $this->getEntityManager()->persist($obj);
    $this->getEntityManager()->flush();
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
   */
  public function likeExists(Program $project, User $user, int $type): bool
  {
    $qb = $this->createQueryBuilder('l');
    $qb->select('count(l)')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type)
    ;

    try {
      $count = $qb->getQuery()->getSingleScalarResult();
    } catch (NonUniqueResultException) {
      return false;
    }

    return ctype_digit($count) && $count > 0;
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
}
