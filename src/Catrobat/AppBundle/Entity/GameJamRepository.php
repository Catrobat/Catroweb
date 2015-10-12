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
            ->where('e.end > :current')
            ->setParameter('current', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->getQuery()->getOneOrNullResult();
    }
}