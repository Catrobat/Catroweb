<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class GameJamRepository extends EntityRepository
{
    public function getCurrentGameJam()
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select('e')
            ->where('e.start < :current')
            ->andWhere('e.end > :current')
            ->setParameter('current', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->getQuery()->getOneOrNullResult();
    }
    
    public function getLatestGameJam()
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
        ->select('e')
        ->orderBy('e.start', 'DESC')
        ->setMaxResults(1)
        ->getQuery()->getOneOrNullResult();
    }
    
}