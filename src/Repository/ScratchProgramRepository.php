<?php

namespace App\Repository;

use App\Entity\ScratchProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ScratchProgramRepository.
 */
class ScratchProgramRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ScratchProgram::class);
  }

  /**
   * @param int[] $scratch_program_ids
   *
   * @return array
   */
  public function getProgramDataByIds(array $scratch_program_ids)
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
