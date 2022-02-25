<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class ProjectMachineTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectMachineTranslation::class);
  }

  /**
   * @throws OptimisticLockException
   * @throws ORMException
   */
  public function invalidateCachedTranslation(Program $project): void
  {
    /** @var ProjectMachineTranslation[] $entries */
    $entries = $this->findBy(['project' => $project]);

    foreach ($entries as $entry) {
      $entry->invalidateCachedTranslation();
      $this->getEntityManager()->persist($entry);
    }
    $this->getEntityManager()->flush();
  }
}
