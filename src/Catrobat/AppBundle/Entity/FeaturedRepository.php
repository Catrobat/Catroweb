<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FeaturedRepository extends EntityRepository
{
    public function getFeaturedPrograms($flavor, $limit = 20, $offset = 0)
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select('e')
            ->where('e.active = true')
            ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
            ->andWhere($qb->expr()->isNotNull('e.program'))
            ->setParameter('flavor', $flavor)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('e.priority', 'DESC')
            ->getQuery()->getResult();
    }

    public function getFeaturedProgramCount($flavor)
    {
        $qb = $this->createQueryBuilder('e');

        return $qb
            ->select($qb->expr()->count('e.id'))
            ->where('e.active = true')
            ->andWhere($qb->expr()->eq('e.flavor', ':flavor'))
            ->andWhere($qb->expr()->isNotNull('e.program'))
            ->setParameter('flavor', $flavor)
            ->getQuery()->getSingleScalarResult();
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
    
}
