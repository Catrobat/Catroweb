<?php

namespace Catrobat\AppBundle\Services;


use Doctrine\ORM\EntityManager;

/**
 * Class CommunityStatisticsService
 * @package Catrobat\AppBundle\Services
 */
class CommunityStatisticsService
{

  /**
   * @var EntityManager
   */
  private $em;

  /**
   * CommunityStatisticsService constructor.
   *
   * @param EntityManager $em
   */
  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  /**
   * Gets the amount of programs currently uploaded and the amount of downloads
   *
   * @return mixed|array array containing key program_count and key downloads
   *                     ready for json response parsing
   */
  public function fetchStatistics()
  {
    $dql = "SELECT COUNT(p.id) AS program_count, SUM(p.downloads) AS downloads FROM Catrobat\AppBundle\Entity\Program p";
    $stats = $this->em->createQuery($dql)
      ->getScalarResult();

    return $stats[0];
  }
}
