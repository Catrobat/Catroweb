<?php

namespace App\Repository;

use App\Entity\UserComment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UserCommentRepository
 * @package App\Repository
 */
class UserCommentRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, UserComment::class);
  }

  /**
   * Returns all UserComments written by a certain User.
   *
   * @param User $user The User which UserComments should be returned
   * @return UserComment[] The UserComments written by the specified User
   */

  public function getCommentsWrittenByUser(User $user)
  {
    $em = $this->getEntityManager();
    $comments = $em->getRepository(UserComment::class)->findBy(['user' => $user]);

    return $comments;
  }
}
