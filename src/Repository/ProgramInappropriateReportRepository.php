<?php

namespace App\Repository;

use App\Entity\ProgramInappropriateReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ProgramInappropriateReportRepository
 * @package App\Repository
 */
class ProgramInappropriateReportRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramInappropriateReport::class);
  }
}
