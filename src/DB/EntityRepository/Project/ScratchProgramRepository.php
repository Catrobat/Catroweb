<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Scratch\ScratchProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScratchProgramRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProgram::class);
  }

  /**
   * @param string[] $scratch_program_ids
   */
  public function getProgramDataByIds(array $scratch_program_ids): array
  {
    $qb = $this->createQueryBuilder('s');

    return $qb
      ->select(['s.id', 's.name', 's.username'])
      ->where('s.id IN (:scratch_program_ids)')
      ->setParameter('scratch_program_ids', $scratch_program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;
  }
}
