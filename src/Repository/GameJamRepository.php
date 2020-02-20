<?php

namespace App\Repository;

use App\Entity\GameJam;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;


/**
 * Class GameJamRepository
 * @package App\Repository
 */
class GameJamRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, GameJam::class);
  }

  /**
   * @return mixed
   * @throws NonUniqueResultException
   * @throws Exception
   */
  public function getCurrentGameJam()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.start < :current')
      ->andWhere('e.end > :current')
      ->setParameter('current', new DateTime(), Types::DATETIME_MUTABLE)
      ->getQuery()->getOneOrNullResult();
  }

  /**
   * @return mixed
   * @throws NonUniqueResultException
   */
  public function getLatestGameJam()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->orderBy('e.start', 'DESC')
      ->setMaxResults(1)
      ->getQuery()->getOneOrNullResult();
  }

  /**
   * @param $flavor
   *
   * @return mixed
   * @throws NonUniqueResultException
   */
  public function getLatestGameJamByFlavor($flavor)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where($qb->expr()->eq('e.flavor', ':flavor'))
      ->orderBy('e.start', 'DESC')
      ->setParameter('flavor', $flavor)
      ->setMaxResults(1)
      ->getQuery()->getOneOrNullResult();
  }

  /**
   * @return mixed
   */
  public function getUsedFlavors()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.flavor')
      ->where($qb->expr()->isNotNull('e.flavor'))
      ->getQuery()->getResult(Query::HYDRATE_ARRAY);
  }
}