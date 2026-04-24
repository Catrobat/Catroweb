<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\User\Comment;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserComment>
 */
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
    return $this->findBy(['studio' => $studio, 'parent_id' => null]);
  }

  public function countStudioComments(?Studio $studio): int
  {
    return $this->count(['studio' => $studio, 'parent_id' => null]);
  }

  /**
   * @param string[] $studioIds
   *
   * @return array<string, int>
   */
  public function countStudioCommentsBatch(array $studioIds): array
  {
    if ([] === $studioIds) {
      return [];
    }

    $em = $this->getEntityManager();

    /** @var array<array{studio_id: string, cnt: string}> $results */
    $results = $em->createQueryBuilder()
      ->select('IDENTITY(c.studio) AS studio_id, COUNT(c) AS cnt')
      ->from(UserComment::class, 'c')
      ->where('c.studio IN (:ids)')
      ->andWhere('c.parent_id IS NULL')
      ->setParameter('ids', $studioIds)
      ->groupBy('c.studio')
      ->getQuery()
      ->getResult()
    ;

    $counts = [];
    foreach ($results as $row) {
      $counts[(string) $row['studio_id']] = (int) $row['cnt'];
    }

    return $counts;
  }

  public function findStudioCommentById(string $comment_id): ?UserComment
  {
    return $this->findOneBy(['id' => $comment_id]);
  }

  public function findCommentReplies(string $comment_id): array
  {
    return $this->findBy(['parent_id' => $comment_id], ['uploadDate' => 'ASC']);
  }

  public function countCommentReplies(string $comment_id): int
  {
    return $this->count(['parent_id' => $comment_id]);
  }

  public function findCommentsByProgramId(string $program_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->addSelect('u')
      ->leftJoin('uc.user', 'u')
      ->where('uc.project = :program_id')
      ->setParameter('program_id', $program_id)
      ->andWhere('uc.parent_id IS NULL')
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult()
    ;
  }

  public function findCommentRepliesByParentId(string $parent_id): array
  {
    $qb = $this->createQueryBuilder('uc');

    return $qb->select('uc')
      ->addSelect('u')
      ->leftJoin('uc.user', 'u')
      ->where('uc.parent_id = :parent_id')
      ->setParameter('parent_id', $parent_id)
      ->orderBy('uc.uploadDate', 'DESC')
      ->getQuery()->getResult()
    ;
  }

  public function getProjectCommentOverviewListData(Project $project): array
  {
    $results = $this->createQueryBuilder('c')
      ->innerJoin('c.user', 'cu')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.auto_hidden as is_reported',
        'c.uploadDate as upload_date',
        'c.parent_id as parent_id',
        'cu.id as user_id',
        'cu.approved as user_approved')
      ->where('c.project = :project')
      ->andWhere('c.parent_id IS NULL')
      ->andWhere('c.auto_hidden = false')
      ->setParameter('project', $project)
      ->getQuery()
      ->getResult()
    ;

    return $this->attachReplyCounts($results);
  }

  public function getProjectCommentsPageData(Project $project, int $limit, ?\DateTimeInterface $cursor_date, ?string $cursor_id): array
  {
    $qb = $this->createQueryBuilder('c');

    $qb->innerJoin('c.user', 'cu')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.auto_hidden as is_reported',
        'c.uploadDate as upload_date',
        'c.parent_id as parent_id',
        'cu.id as user_id',
        'cu.approved as user_approved')
      ->where('c.project = :project')
      ->andWhere('c.parent_id IS NULL')
      ->andWhere('c.auto_hidden = false')
      ->setParameter('project', $project)
      ->orderBy('c.uploadDate', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if ($cursor_date instanceof \DateTimeInterface && null !== $cursor_id) {
      $qb->andWhere(
        $qb->expr()->orX(
          $qb->expr()->lt('c.uploadDate', ':cursorDate'),
          $qb->expr()->andX(
            $qb->expr()->eq('c.uploadDate', ':cursorDate'),
            $qb->expr()->lt('c.id', ':cursorId')
          )
        )
      )
        ->setParameter('cursorDate', $cursor_date)
        ->setParameter('cursorId', $cursor_id)
      ;
    }

    $results = $qb->getQuery()->getResult();
    $has_more = count($results) > $limit;
    if ($has_more) {
      $results = array_slice($results, 0, $limit);
    }

    return [
      'comments' => $this->attachReplyCounts($results),
      'has_more' => $has_more,
    ];
  }

  public function getCommentRepliesPageData(string $comment_id, int $limit, ?\DateTimeInterface $cursor_date, ?string $cursor_id): array
  {
    $qb = $this->createQueryBuilder('c');

    $qb->innerJoin('c.user', 'cu')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.auto_hidden as is_reported',
        'c.uploadDate as upload_date',
        'c.parent_id as parent_id',
        'cu.id as user_id',
        'cu.approved as user_approved')
      ->where('c.parent_id = :parentId')
      ->andWhere('c.auto_hidden = false')
      ->setParameter('parentId', $comment_id)
      ->orderBy('c.uploadDate', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if ($cursor_date instanceof \DateTimeInterface && null !== $cursor_id) {
      $qb->andWhere(
        $qb->expr()->orX(
          $qb->expr()->lt('c.uploadDate', ':cursorDate'),
          $qb->expr()->andX(
            $qb->expr()->eq('c.uploadDate', ':cursorDate'),
            $qb->expr()->lt('c.id', ':cursorId')
          )
        )
      )
        ->setParameter('cursorDate', $cursor_date)
        ->setParameter('cursorId', $cursor_id)
      ;
    }

    $results = $qb->getQuery()->getResult();
    $has_more = count($results) > $limit;
    if ($has_more) {
      $results = array_slice($results, 0, $limit);
    }

    return [
      'comments' => $this->attachReplyCounts($results),
      'has_more' => $has_more,
    ];
  }

  /**
   * @throws NonUniqueResultException
   */
  public function getProjectCommentDetailViewData(string $comment_id): ?array
  {
    $result = $this->createQueryBuilder('c')
      ->innerJoin('c.user', 'cu')
      ->innerJoin('c.project', 'cp')
      ->select(
        'c.id',
        'c.username',
        'c.text',
        'c.is_deleted',
        'c.auto_hidden as is_reported',
        'c.uploadDate as upload_date',
        'c.parent_id as parent_id',
        'cp.id as program_id',
        'cu.id as user_id',
        'cu.approved as user_approved'
      )
      ->where('c.id = :id')
      ->setParameter('id', $comment_id)
      ->getQuery()
      ->getOneOrNullResult()
    ;

    if (null === $result) {
      return null;
    }

    $results = $this->attachReplyCounts([$result]);

    return $results[0];
  }

  /**
   * Batch-loads reply counts for comment rows and attaches them as 'number_of_replies'.
   *
   * @param array<int, array<string, mixed>> $results
   *
   * @return array<int, array<string, mixed>>
   */
  private function attachReplyCounts(array $results): array
  {
    $commentIds = array_column($results, 'id');
    if ([] === $commentIds) {
      return $results;
    }

    $replyCounts = $this->createQueryBuilder('r')
      ->select('r.parent_id AS parent_id, COUNT(r.id) AS cnt')
      ->where('r.parent_id IN (:ids)')
      ->setParameter('ids', $commentIds)
      ->groupBy('r.parent_id')
      ->getQuery()
      ->getResult()
    ;

    $replyMap = [];
    foreach ($replyCounts as $row) {
      $replyMap[$row['parent_id']] = (int) $row['cnt'];
    }

    foreach ($results as &$row) {
      $row['number_of_replies'] = $replyMap[$row['id']] ?? 0;
    }

    return $results;
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
        'cu.id as user_id'
      )
      ->where('c.parent_id = :parentId')
      ->setParameter('parentId', $parent_comment_id)
      ->getQuery()
      ->getResult()
    ;
  }
}
