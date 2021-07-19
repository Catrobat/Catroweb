<?php

namespace App\Repository\Studios;

use App\Entity\Studio;
use App\Entity\StudioProgram;
use App\Entity\StudioUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudioRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Studio::class);
  }

  public function findAllStudiosWithUsersAndProjectsCount(): array
  {
    $qb_su = $this->getEntityManager()->createQueryBuilder();
    $qb_su->select('COUNT(su)')
      ->from(StudioUser::class, 'su')
      ->where('s.id = su.studio')
    ;
    $qb_sp = $this->getEntityManager()->createQueryBuilder();
    $qb_sp->select('COUNT(sp)')
      ->from(StudioProgram::class, 'sp')
      ->where('s.id = sp.studio')
    ;
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('s.id, s.name, s.description, s.is_public, s.is_enabled, s.allow_comments, s.cover_path , 
    ('.$qb_su->getDQL().')  AS studio_users'.', ('.$qb_sp->getDQL().') AS studio_projects')
      ->from(Studio::class, 's')
    ;

    return $qb->getQuery()->getArrayResult();
  }

  public function findStudioById(string $id): ?Studio
  {
    return $this->findOneBy(['id' => $id]);
  }
}
