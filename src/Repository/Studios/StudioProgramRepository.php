<?php

namespace App\Repository\Studios;

use App\Entity\Program;
use App\Entity\Studio;
use App\Entity\StudioProgram;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudioProgramRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioProgram::class);
  }

  public function findAllStudioProjects(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio]);
  }

  public function findStudioProject(Studio $studio, Program $program): ?StudioProgram
  {
    return $this->findOneBy(['studio' => $studio, 'program' => $program]);
  }

  public function findStudioProjectsCount(?Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }

  public function findStudioUserProjectsCount(?Studio $studio, ?User $user): int
  {
    return $this->count(['studio' => $studio, 'user' => $user]);
  }
}
