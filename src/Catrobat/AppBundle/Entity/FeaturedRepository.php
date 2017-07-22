<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FeaturedRepository extends EntityRepository
{
  public function getFeaturedPrograms($flavor, $limit = 20, $offset = 0, $max_version = 0)
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->setParameter('flavor', $flavor)
      ->setFirstResult($offset)
      ->setMaxResults($limit);

    if ($max_version !== 0)
    {
      $qb
        ->andWhere($qb
          ->expr()->lte('e.language_version', ':max_version'))
        ->setParameter('max_version', $max_version);
    }

    $qb->orderBy('e.priority', 'DESC');

    return $qb->getQuery()->getResult();
  }

  public function getFeaturedProgramCount($flavor, $max_version = 0)
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select($qb->expr()->count('e.id'))
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->setParameter('flavor', $flavor);
    if ($max_version !== 0)
    {
      $qb
        ->andWhere($qb
          ->expr()->lte('e.language_version', ':max_version'))
        ->setParameter('max_version', $max_version);
    }
    return $qb->getQuery()->getSingleScalarResult();
  }

  public function getFeaturedItems($flavor, $limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->setParameter('flavor', $flavor)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->orderBy('e.priority', 'DESC')
      ->getQuery()->getResult();
  }

  public function getFeaturedItemCount($flavor)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select($qb->expr()->count('e.id'))
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
      ->setParameter('flavor', $flavor)
      ->getQuery()->getSingleScalarResult();
  }
  
   public function isFeatured($program)
    {
        /* @var \Catrobat\AppBundle\Entity\Program $program */
        $qb = $this->createQueryBuilder('e');
        $qb
            ->where($qb->expr()->eq('e.program', ':program'))
            ->setParameter('program', $program);
        ;
        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result == null)
        {
            return false;
        }
        return true;
    }
}
