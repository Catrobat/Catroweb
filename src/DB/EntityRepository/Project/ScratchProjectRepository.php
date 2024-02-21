<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Scratch\ScratchProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScratchProjectRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProject::class);
  }

  /**
   * @param string[] $scratch_project_ids
   */
  public function getProjectDataByIds(array $scratch_project_ids): array
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select(['s.id', 's.name', 's.username'])
      ->where('s.id IN (:scratch_project_ids)')
      ->setParameter('scratch_project_ids', $scratch_project_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }
}
