<?php

namespace App\Repository;

use App\Entity\Studio;
use App\Entity\User;
use App\Entity\UserComment;
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

  public function findStudioCommentsCount(?Studio $studio): int
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

  public function findCommentRepliesCount(int $comment_id): int
  {
    return $this->count(['parent_id' => $comment_id]);
  }
}
