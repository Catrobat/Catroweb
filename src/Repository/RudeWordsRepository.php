<?php

namespace App\Repository;

use App\Entity\RudeWord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class RudeWordsRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, RudeWord::class);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function contains(array $array): bool
  {
    $qb = $this->createQueryBuilder('e');
    $qb->select($qb->expr()->count('e.word'))
      ->where($qb->expr()->in('e.word', '?1'))
      ->setParameter(1, $array)
    ;
    $result = $qb->getQuery()->getSingleScalarResult();

    return $result > 0;
  }
}
