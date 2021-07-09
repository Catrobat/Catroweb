<?php

namespace App\Repository\Studios;

use App\Entity\Studio;
use App\Entity\StudioUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudioUserRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioUser::class);
  }

  public function findAllStudioUsers(?Studio $studio): array
  {
    return $this->findBy(['studio' => $studio]);
  }

  public function findStudioUser(?User $user, Studio $studio): ?StudioUser
  {
    return $this->findOneBy(['studio' => $studio, 'user' => $user]);
  }

  public function findStudioUsersCount(?Studio $studio): int
  {
    return $this->count(['studio' => $studio]);
  }
}
