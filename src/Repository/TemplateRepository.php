<?php

namespace App\Repository;

use App\Entity\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class TemplateRepository
 * @package App\Repository
 */
class TemplateRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Template::class);
  }
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
