<?php
namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProgramDownloadsRepository extends EntityRepository
{
    public function getProgramDownloadStatistics($program_id)
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->select('e')
            ->where('e.id = ' . $program_id)
            ->setParameter('current', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->getQuery()->getOneOrNullResult();
    }
}
