<?php

namespace App\Repository;

use App\Entity\RudeWord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class RudeWordsRepository
 * @package App\Repository
 */
class RudeWordsRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, RudeWord::class);
  }

  /**
   * @param $array
   *
   * @return bool
   * @throws NonUniqueResultException
   */
  public function contains($array)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->select($qb->expr()->count('e.word'))
      ->where($qb->expr()->in('e.word', '?1'))
      ->setParameter(1, $array);
    $result = $qb->getQuery()->getSingleScalarResult();

    return $result > 0;
  }
}
