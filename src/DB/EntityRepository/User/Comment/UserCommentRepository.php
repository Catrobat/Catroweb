<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Comment;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
    return $this->count(['parent_id' => $comment_id]);
  }

  public function findCommentsByProgramId(string $program_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->where('uc.program = :program_id')
      ->setParameter('program_id', $program_id)
      ->andWhere($qb->expr()->orX()->addMultiple([
        $qb->expr()->isNull('uc.parent_id'),
        $qb->expr()->eq('uc.parent_id', 0),
      ]))
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult()
    ;
  }

  public function findCommentRepliesByParentId(string $parent_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->where('uc.parent_id = :parent_id')
      ->setParameter('parent_id', $parent_id)
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult()
    ;
  }

  public function getProjectCommentOverviewListData(Program $project): array
  {
    return $this->createQueryBuilder('c')
      ->innerJoin('c.user', 'cu')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.uploadDate as upload_date',
        'cu.id as user_id',
        'cu.avatar as user_avatar',
        '(SELECT COUNT(c2.id) FROM '.UserComment::class.' c2 WHERE c2.parent_id = c.id) AS number_of_replies')
      ->where('c.program = :program')
      ->andWhere('c.parent_id IS NULL')
      ->setParameter('program', $project)
      ->getQuery()
      ->getResult()
    ;
  }

  /**
   * @throws NonUniqueResultException
   */
  public function getProjectCommentDetailViewData(string $comment_id): array
  {
    return $this->createQueryBuilder('c')
      ->innerJoin('c.user', 'cu')
      ->innerJoin('c.program', 'cp')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.uploadDate as upload_date',
        'cp.id as program_id',
        'cu.id as user_id',
        'cu.avatar as user_avatar'
      )
      ->where('c.id = :id')
      ->setParameter('id', $comment_id)
      ->getQuery()
      ->getOneOrNullResult()
    ;
  }

  public function getProjectCommentDetailViewCommentListData(string $parent_comment_id): array
  {
    return $this->createQueryBuilder('c')
      ->innerJoin('c.user', 'cu')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.uploadDate as upload_date',
        'cu.id as user_id',
        'cu.avatar as user_avatar'
      )
      ->where('c.parent_id = :parentId')
      ->setParameter('parentId', $parent_comment_id)
      ->getQuery()
      ->getResult()
    ;
  }
}
