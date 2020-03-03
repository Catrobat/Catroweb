<?php

namespace App\Repository;

use App\Entity\StarterCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StarterCategoryRepository.
 */
class StarterCategoryRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StarterCategory::class);
  }
}
