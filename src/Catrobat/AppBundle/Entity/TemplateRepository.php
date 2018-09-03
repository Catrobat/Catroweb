<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TemplateRepository extends EntityRepository
{

  public function findByActive($active)
  {
    $qb = $this->createQueryBuilder('e');

    $result = $qb
      ->select('e')
      ->where($qb->expr()->eq('e.active', $qb->expr()->literal($active)))
      ->getQuery()
      ->getResult();

    return $result;
  }
}
