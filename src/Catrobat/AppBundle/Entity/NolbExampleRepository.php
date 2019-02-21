<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class NolbExampleRepository
 * @package Catrobat\AppBundle\Entity
 */
class NolbExampleRepository extends EntityRepository
{
  /**
   * @param int $limit
   * @param int $offset
   *
   * @return mixed
   */
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

  /**
   * @return int
   */
  public function getActiveProgramsCount()
  {
    $qb = $this->createQueryBuilder('e');

    return count($qb
      ->select('e')
      ->where('e.active = true')
      ->andWhere($qb->expr()->isNotNull('e.program'))
      ->getQuery()->getResult());
  }

  /**
   * @param bool $for_female
   * @param int  $limit
   * @param int  $offset
   *
   * @return mixed
   */
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

  /**
   * @param $program_id
   *
   * @return object|null
   */
  public function getIfNolbExampleProgram($program_id)
  {
    return $this->findOneBy(['program' => $program_id]);
  }

}
