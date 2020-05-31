<?php

namespace App\Catrobat\Services;

use Doctrine\ORM\EntityManagerInterface;

class CommunityStatisticsService
{
  private EntityManagerInterface $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * Gets the amount of programs currently uploaded and the amount of downloads.
   *
   * @return mixed|array array containing key program_count and key downloads
   *                     ready for json response parsing
   */
  public function fetchStatistics()
  {
    $dql = 'SELECT COUNT(p.id) AS program_count, SUM(p.downloads) AS downloads FROM App\\Entity\\Program p';
    $stats = $this->em->createQuery($dql)
      ->getScalarResult()
    ;

    return $stats[0];
  }
}
