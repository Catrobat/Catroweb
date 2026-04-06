<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Studio>
 */
class StudioRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Studio::class);
  }

  public function findAllStudiosWithUsersAndProjectsCount(): array
  {
    $qb_su = $this->getEntityManager()->createQueryBuilder();
    $qb_su->select('COUNT(su)')
      ->from(StudioUser::class, 'su')
      ->where('s.id = su.studio')
    ;
    $qb_sp = $this->getEntityManager()->createQueryBuilder();
    $qb_sp->select('COUNT(sp)')
      ->from(StudioProgram::class, 'sp')
      ->where('s.id = sp.studio')
    ;
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('s.id, s.name, s.description, s.is_public, s.is_enabled, s.allow_comments, s.cover_path ,
    ('.$qb_su->getDQL().')  AS studio_users, ('.$qb_sp->getDQL().') AS studio_projects')
      ->from(Studio::class, 's')
      ->andWhere('s.auto_hidden = false')
    ;

    return $qb->getQuery()->getArrayResult();
  }

  /**
   * Loads all visible studios with user join status in a single query (N+1 fix).
   *
   * @return array<array-key, array<string, mixed>>
   */
  public function findStudiosWithUserContext(?User $user): array
  {
    $em = $this->getEntityManager();

    $qb_su = $em->createQueryBuilder();
    $qb_su->select('COUNT(su)')
      ->from(StudioUser::class, 'su')
      ->where('s.id = su.studio')
    ;

    $qb_sp = $em->createQueryBuilder();
    $qb_sp->select('COUNT(sp)')
      ->from(StudioProgram::class, 'sp')
      ->where('s.id = sp.studio')
    ;

    $qb = $em->createQueryBuilder();
    $qb->select(
      's.id',
      's.name',
      's.description',
      's.is_public',
      's.is_enabled',
      's.allow_comments',
      's.cover_path',
      '('.$qb_su->getDQL().') AS studio_users',
      '('.$qb_sp->getDQL().') AS studio_projects',
    )
      ->from(Studio::class, 's')
      ->andWhere('s.auto_hidden = false')
    ;

    if (null !== $user) {
      $qb_member = $em->createQueryBuilder();
      $qb_member->select('COUNT(mu)')
        ->from(StudioUser::class, 'mu')
        ->where('mu.studio = s.id')
        ->andWhere('mu.user = :user')
      ;

      $qb_join_status = $em->createQueryBuilder();
      $qb_join_status->select('jr.status')
        ->from(StudioJoinRequest::class, 'jr')
        ->where('jr.studio = s.id')
        ->andWhere('jr.user = :user')
        ->setMaxResults(1)
      ;

      $qb->addSelect(
        '('.$qb_member->getDQL().') AS is_member',
        'COALESCE(('.$qb_join_status->getDQL()."), 'false') AS join_status",
      )
        ->setParameter('user', $user)
      ;
    } else {
      $qb->addSelect(
        '0 AS is_member',
        "'false' AS join_status",
      );
    }

    $results = $qb->getQuery()->getArrayResult();

    return array_map(static function (array $row): array {
      $row['is_joined'] = ((int) $row['is_member']) > 0;
      $row['status'] = $row['join_status'];
      unset($row['is_member'], $row['join_status']);

      return $row;
    }, $results);
  }

  public function findStudioById(string $id): ?Studio
  {
    return $this->findOneBy(['id' => $id]);
  }

  public function findStudioByName(string $name): ?Studio
  {
    return $this->findOneBy(['name' => $name]);
  }
}
