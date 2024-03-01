<?php

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StudioJoinRequestRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioJoinRequest::class);
  }

  public function findPendingJoinRequests(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio, 'status' => StudioJoinRequest::STATUS_PENDING]);
  }

  public function findApprovedJoinRequests(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio, 'status' => StudioJoinRequest::STATUS_APPROVED]);
  }

  public function findDeclinedJoinRequests(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio, 'status' => StudioJoinRequest::STATUS_DECLINED]);
  }

  public function findJoinRequestById(int $joinRequestId): ?StudioJoinRequest
  {
    return $this->findOneBy(['id' => $joinRequestId]);
  }

  public function findJoinRequestByUserAndStudio(User $user, Studio $studio): ?StudioJoinRequest
  {
    return $this->findOneBy(['user' => $user, 'studio' => $studio]);
  }

  public function deleteJoinRequestById(int $joinRequestId): void
  {
    $joinRequest = $this->findJoinRequestById($joinRequestId);

    if (null !== $joinRequest) {
      $entityManager = $this->getEntityManager();
      $entityManager->remove($joinRequest);
      $entityManager->flush();
    }
  }
}
