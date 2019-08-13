<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class TagRepository
 * @package App\Repository
 */
class TagRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Tag::class);
  }

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
