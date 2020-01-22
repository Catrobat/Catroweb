<?php

namespace App\Repository;

use App\Entity\Program;
use App\Entity\ProgramLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;


/**
 * Class ProgramLikeRepository
 * @package App\Repository
 */
class ProgramLikeRepository extends ServiceEntityRepository
{

  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramLike::class);
  }

  /**
   * @param int $program_id
   * @param int $type
   *
   * @return int
   */
  public function likeTypeCount($program_id, $type)
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
      ->getResult();

    return count($result);
  }

  /**
   * @param int $project_id
   *
   * @return array
   */
  public function likeTypesOfProject($project_id)
  {
    $qb = $this->createQueryBuilder('l');

    $qb
      ->select('l.type')->distinct()
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $project_id);

    return array_map(function ($x) {
      return $x['type'];
    }, $qb->getQuery()->getResult());
  }

  /**
   * @param int $program_id
   *
   * @return int
   */
  public function totalLikeCount($program_id)
  {
    $qb = $this->createQueryBuilder('l');

    $result = $qb
      ->select('l')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->setParameter(':program_id', $program_id)
      ->distinct()
      ->getQuery()
      ->getResult();

    return count($result);
  }

  /**
   * @param $user_ids array
   * @param $exclude_user_id
   * @param $exclude_program_ids
   * @param $flavor
   *
   * @return ProgramLike[]
   */
  public function getLikesOfUsers($user_ids, $exclude_user_id, $exclude_program_ids, $flavor)
  {
    $qb = $this->createQueryBuilder('l');

    return $qb
      ->select('l')
      ->innerJoin('App\Entity\Program', 'p', Join::WITH, $qb->expr()->eq('p.id', 'l.program'))
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
      ->getResult();
  }

  /**
   * @param Program $project
   * @param User    $user
   * @param         $type
   *
   * @throws ORMException
   */
  public function addLike(Program $project, User $user, $type)
  {
    if ($this->likeExists($project, $user, $type))
    {
      // Like exists already, nothing to do.
      return;
    }

    $obj = new ProgramLike($project, $user, $type);
    $this->getEntityManager()->persist($obj);
    $this->getEntityManager()->flush();
  }

  /**
   * @param Program $project
   * @param User    $user
   * @param         $type
   */
  public function removeLike(Program $project, User $user, $type)
  {
    $qb = $this->createQueryBuilder('l');
    $qb->delete()
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type);

    $qb->getQuery()->execute();
  }

  /**
   * @param Program $project
   * @param User    $user
   * @param         $type
   *
   * @return bool
   */
  public function likeExists(Program $project, User $user, $type)
  {
    $qb = $this->createQueryBuilder('l');
    $qb->select('count(l)')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->eq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type);

    try
    {
      $count = $qb->getQuery()->getSingleScalarResult();
    } catch (NonUniqueResultException $exception)
    {
      return false;
    }

    return ctype_digit($count) && $count > 0;
  }

  /**
   * @param Program $project
   * @param User    $user
   * @param         $type
   *
   * @return bool
   * @throws NonUniqueResultException
   */
  public function areThereOtherLikeTypes(Program $project, User $user, $type)
  {
    $qb = $this->createQueryBuilder('l');
    $qb->select('count(l)')
      ->where($qb->expr()->eq('l.program_id', ':program_id'))
      ->andWhere($qb->expr()->eq('l.user_id', ':user_id'))
      ->andWhere($qb->expr()->neq('l.type', ':type'))
      ->setParameter(':program_id', $project->getId())
      ->setParameter(':user_id', $user->getId())
      ->setParameter(':type', $type);

    $count = $qb->getQuery()->getSingleScalarResult();

    return ctype_digit($count) && $count > 0;
  }

}
