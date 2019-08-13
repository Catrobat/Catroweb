<?php

namespace App\Repository;

use App\Entity\Extension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * Class ExtensionRepository
 * @package App\Repository
 */
class ExtensionRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Extension::class);
  }

  /**
   * @return mixed
   */
  public function getAllExtensionsPrefix()
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.prefix')
      ->getQuery()
      ->getResult();
  }

  /**
   * @param $name
   *
   * @return mixed
   */
  public function getExtensionByName($name)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where($qb->expr()->eq('e.name', ':name'))
      ->setParameter('name', $name)
      ->getQuery()
      ->getResult();
  }
}
