<?php

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class StudioProjectRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioProject::class);
  }

  public function findAllStudioProjects(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio]);
  }

  public function findStudioProject(Studio $studio, Project $project): ?StudioProject
  {
    return $this->findOneBy(['studio' => $studio, 'project' => $project]);
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
