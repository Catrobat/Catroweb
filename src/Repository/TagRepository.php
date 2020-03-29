<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Tag::class);
  }

  /**
   * @return mixed
   */
  public function getConstantTags(string $language)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.'.$language)
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @return mixed
   */
  public function getTagsWithProgramIdAndLanguage(string $program_id, string $language)
  {
    $qb = $this->createQueryBuilder('e');

    return $qb
      ->select('e.'.$language)
      ->leftJoin('e.programs', 'p')
      ->andWhere($qb->expr()->eq('p.id', ':id'))
      ->setParameter('id', $program_id)
      ->getQuery()
      ->getResult()
    ;
  }
}
