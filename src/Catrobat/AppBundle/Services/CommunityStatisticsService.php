<?php
/**
 * Copyright (c) 2017. Catrobat
 * Imagine that each Catrobat program is a cake, a very special cake that comes with its recipe (programming blocks). All members of the Catrobat community share their cakes along with their recipes. This means that you can enjoy the cakes and learn how to make them yourself!
 * There are no secret recipes: the instructions on how to make these cakes are open for anyone to use, reuse, modify, and serve as inspiration for new ideas... I mean cakes.
 *
 * You can eat the cakes as well as copy other people's recipes to make your own, maybe with different ingredients. This freedom comes with two simple requirements:
 *
 * share your cakes along with the recipe
 * give credit to those who inspired you
 *
 *
 * In setting up the Catrobat community, we decided to adopt this approach since we believe that it supports learning and creativity within the community. By sharing recipes and ingredients (scripts and artwork), people can build upon one another's ideas and everyone will benefit.
 *
 * In designing the Catrobat website, we included features to encourage people to share and to give credit to others. On each program page, you can always download the original scripts for the program. If you remix a program (modifying the scripts or artwork, and sharing the result), we encourage you to give credit in the Program Notes, mentioning the people and program that inspired you.
 *
 * Learn more about the terms of use of the Catrobat online community on https://share.catrob.at/pocketcode/termsOfUse.
 *
 * Version 1.1, 2 April 2013
 */

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\CatroNotification;
use Doctrine\ORM\EntityManager;

class CommunityStatisticsService
{

  private $em;

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
