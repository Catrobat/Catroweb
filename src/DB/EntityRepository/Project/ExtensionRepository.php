<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Extension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExtensionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Extension::class);
  }

  public function getExtensionByInternalTitle(string $internal_title): mixed
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where($qb->expr()->eq('e.internal_title', ':internal_title'))
      ->setParameter('internal_title', $internal_title)
      ->getQuery()
      ->getResult()
    ;
  }

  public function getActiveExtensions(): array
  {
    $extensions = $this->findBy([
      'enabled' => true,
    ]);

    $active_extensions = [];
    /** @var Extension $extension */
    foreach ($extensions as $extension) {
      $active_extensions[] = $extension;
    }

    return $active_extensions;
  }
}
