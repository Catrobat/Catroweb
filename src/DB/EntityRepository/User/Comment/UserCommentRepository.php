<?php

namespace App\DB\EntityRepository\User\Comment;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserCommentRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserComment::class);
  }

  /**
   * Returns all UserComments written by a certain User.
   *
   * @param User $user The User which UserComments should be returned
   *
   * @return UserComment[] The UserComments written by the specified User
   */
  public function getCommentsWrittenByUser(User $user): array
  {
    $em = $this->getEntityManager();

    return $em->getRepository(UserComment::class)->findBy(['user' => $user]);
  }

  public function findAllStudioComments(?Studio $studio): array
  {
    return $this->findBy(['studio' => $studio, 'parent_id' => 0]);
  }

  public function countStudioComments(?Studio $studio): int
  {
    return $this->count(['studio' => $studio, 'parent_id' => 0]);
  }

  public function findStudioCommentById(int $comment_id): ?UserComment
  {
    return $this->findOneBy(['id' => $comment_id]);
  }

  public function findCommentReplies(int $comment_id): array
  {
    return $this->findBy(['parent_id' => $comment_id]);
  }

  public function countCommentReplies(int $comment_id): int
  {
    $qb = $this->createQueryBuilder('uc');

    return $this->count(['parent_id' => $comment_id]);
  }

  public function findCommentsByProgramId(string $program_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->where('uc.program = :program_id')
      ->setParameter('program_id', $program_id)
      ->andWhere($qb->expr()->isNull('uc.parent_id'))
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult();
  }

  public function findCommentRepliesByParentId(string $parent_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->where('uc.parent_id = :parent_id')
      ->setParameter('parent_id', $parent_id)
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult();
  }
}
