<?php

namespace App\Repository\Studios;

use App\Entity\Program;
use App\Entity\Studio;
use App\Entity\StudioProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

  public function countStudioProjects(?Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }

  public function countStudioUserProjects(?Studio $studio, ?UserInterface $user): int
  {
    return $this->count(['studio' => $studio, 'user' => $user]);
  }
}
