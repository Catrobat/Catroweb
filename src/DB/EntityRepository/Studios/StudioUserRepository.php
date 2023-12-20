<?php

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class StudioUserRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioUser::class);
  }

  public function findAllStudioUsers(?Studio $studio): array
  {
    return $this->findBy(['studio' => $studio, 'status' => StudioUser::STATUS_ACTIVE]);
  }

  public function findStudioAdmin(?Studio $studio): ?StudioUser
  {
    return $this->findOneBy(['studio' => $studio, 'role' => StudioUser::ROLE_ADMIN]);
  }

  public function findStudioUser(?UserInterface $user, Studio $studio): ?StudioUser
  {
    return $this->findOneBy(['studio' => $studio, 'user' => $user]);
  }

  public function countStudioUsers(?Studio $studio): int
  {
    return $this->count(['studio' => $studio, 'status' => 'active']);
  }
}
