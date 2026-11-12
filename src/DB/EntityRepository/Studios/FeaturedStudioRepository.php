<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\FeaturedStudio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeaturedStudio>
 */
class FeaturedStudioRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, FeaturedStudio::class);
  }
}
