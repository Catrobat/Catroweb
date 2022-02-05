<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProgramInappropriateReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProgramInappropriateReportRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramInappropriateReport::class);
  }
}
