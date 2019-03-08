<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class TemplateRepository
 * @package App\Repository
 */
class TemplateRepository extends EntityRepository
{

  /**
   * @param $active
   *
   * @return mixed
   */
  public function findByActive($active)
  {
    $qb = $this->createQueryBuilder('e');

    $result = $qb
      ->select('e')
      ->where($qb->expr()->eq('e.active', $qb->expr()->literal($active)))
      ->getQuery()
      ->getResult();

    return $result;
  }
}
