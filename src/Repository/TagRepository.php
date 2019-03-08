<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class TagRepository
 * @package App\Repository
 */
class TagRepository extends EntityRepository
{
  /**
   * @param $language
   *
   * @return mixed
   */
  public function getConstantTags($language)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.' . $language)
      ->getQuery()
      ->getResult();
  }

  /**
   * @param $program_id
   * @param $language
   *
   * @return mixed
   */
  public function getTagsWithProgramIdAndLanguage($program_id, $language)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.' . $language)
      ->leftJoin('e.programs', 'p')
      ->andWhere($qb->expr()->eq('p.id', ':id'))
      ->setParameter('id', $program_id)
      ->getQuery()
      ->getResult();
  }
}
