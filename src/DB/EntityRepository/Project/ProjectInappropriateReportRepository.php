<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\ProjectInappropriateReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectInappropriateReportRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectInappropriateReport::class);
  }
}
