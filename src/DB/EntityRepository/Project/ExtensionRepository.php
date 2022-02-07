<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Extension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExtensionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Extension::class);
  }

  /**
   * @return mixed
   */
  public function getExtensionByInternalTitle(string $internal_title)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where($qb->expr()->eq('e.internal_title', ':internal_title'))
      ->setParameter('internal_title', $internal_title)
      ->getQuery()
      ->getResult()
    ;
  }
}
