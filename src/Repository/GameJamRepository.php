<?php

namespace App\Repository;

use App\Entity\GameJam;
use App\Utils\TimeUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class GameJamRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, GameJam::class);
  }

  /**
   * @throws NonUniqueResultException
   * @throws Exception
   *
   * @return mixed
   */
  public function getCurrentGameJam()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.start < :current')
      ->andWhere('e.end > :current')
      ->setParameter('current', TimeUtils::getDateTime(), Types::DATETIME_MUTABLE)
      ->getQuery()->getOneOrNullResult();
  }

  /**
   * @throws NonUniqueResultException
   *
   * @return mixed
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
   * @throws NonUniqueResultException
   *
   * @return mixed
   */
  public function getLatestGameJamByFlavor(string $flavor)
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
