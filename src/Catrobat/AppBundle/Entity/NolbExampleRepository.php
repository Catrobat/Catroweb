<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NolbExampleRepository extends EntityRepository
{
  public function getActivePrograms($limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()->getResult();
  }

  public function getActiveProgramsCount()
  {
    $qb = $this->createQueryBuilder('e');

    return count($qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->getQuery()->getResult());
  }

  public function getGenderPrograms($for_female = false, $limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->eq('e.female_user', ':female'))
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->setFirstResult($offset)
      ->setParameter('female', $for_female)
      ->setMaxResults($limit)
      ->getQuery()->getResult();
  }

  public function getIfNolbExampleProgram($program_id)
  {
    return $this->findOneBy(array('program' => $program_id));
  }

}
