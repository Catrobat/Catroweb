<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FeaturedRepository extends EntityRepository
{
    function getFeaturedPrograms($limit = 20, $offset = 0)
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select('e')
            ->where($qb->expr()->eq('e.active',true))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
    
    function getFeaturedProgramCount()
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select($qb->expr()->count('e.id'))
            ->where("e.active = true")
            ->getQuery()->getSingleScalarResult();
    }
    
}