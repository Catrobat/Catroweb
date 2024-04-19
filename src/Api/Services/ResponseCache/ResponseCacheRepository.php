<?php

declare(strict_types=1);

namespace App\Api\Services\ResponseCache;

use App\DB\Entity\Api\ResponseCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResponseCacheRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ResponseCache::class);
  }
}
