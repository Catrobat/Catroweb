<?php

namespace App\Repository;

use App\Entity\StarterCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class StarterCategoryRepository
 * @package App\Repository
 */
class StarterCategoryRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StarterCategory::class);
  }
}
