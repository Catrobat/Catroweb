<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FeaturedRepository extends EntityRepository
{
    function getFeaturedPrograms($flavor, $limit = 20, $offset = 0)
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select('e')
            ->where($qb->expr()->eq('e.active',true))
            ->andWhere($qb->expr()->eq("e.flavor", ":flavor"))
            ->setParameter("flavor", $flavor)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
    
    function getFeaturedProgramCount($flavor)
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select($qb->expr()->count('e.id'))
            ->where("e.active = true")
            ->andWhere($qb->expr()->eq("e.flavor", ":flavor"))
            ->setParameter("flavor", $flavor)
            ->getQuery()->getSingleScalarResult();
    }
    
}