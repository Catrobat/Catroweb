<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RudeWordsRepository
 * @package App\Repository
 */
class RudeWordsRepository extends EntityRepository
{
  /**
   * @param $array
   *
   * @return bool
   * @throws \Doctrine\ORM\NonUniqueResultException
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
