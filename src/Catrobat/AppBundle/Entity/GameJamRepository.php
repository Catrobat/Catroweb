<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class GameJamRepository extends EntityRepository
{
  public function getCurrentGameJam()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.start < :current')
      ->andWhere('e.end > :current')
      ->setParameter('current', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
      ->getQuery()->getOneOrNullResult();
  }

  public function getLatestGameJam()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->orderBy('e.start', 'DESC')
      ->setMaxResults(1)
      ->getQuery()->getOneOrNullResult();
  }

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

  public function getUsedFlavors()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.flavor')
      ->where($qb->expr()->isNotNull('e.flavor'))
      ->getQuery()->getResult(Query::HYDRATE_ARRAY);
  }
}