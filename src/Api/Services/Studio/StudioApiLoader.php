<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Storage\ScreenshotRepository;
use App\Studio\StudioManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;

class StudioApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly StudioManager $studio_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly UserManager $user_manager,
    private readonly ScreenshotRepository $screenshot_repository,
  ) {
  }

  public function loadStudioByID(string $id): ?Studio
  {
    return $this->studio_manager->findStudioById($id);
  }

  public function loadVisibleStudio(string $id): ?Studio
  {
    $studio = $this->studio_manager->findStudioById($id);

    if (!$studio instanceof Studio || $studio->getAutoHidden() || !$studio->isIsEnabled()) {
      return null;
    }

    return $studio;
  }

  public function loadStudioUser(?User $user, Studio $studio): ?StudioUser
  {
    return $this->studio_manager->findStudioUser($user, $studio);
  }

  /**
   * @return array{studios: Studio[], has_more: bool}
   */
  public function loadStudiosPage(int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('s')
      ->from(Studio::class, 's')
      ->where('s.auto_hidden = false')
      ->andWhere('s.is_enabled = true')
      ->orderBy('s.created_on', 'DESC')
      ->addOrderBy('s.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      // Studios use UUID primary keys, so cursor is offset-based: base64(row_offset)
      $qb->setFirstResult($cursor_id);
    }

    /** @var Studio[] $studios */
    $studios = $qb->getQuery()->getResult();

    $has_more = count($studios) > $limit;
    if ($has_more) {
      array_pop($studios);
    }

    return [
      'studios' => $studios,
      'has_more' => $has_more,
    ];
  }

  /**
   * @return array{members: StudioUser[], has_more: bool}
   */
  public function loadMembersPage(Studio $studio, int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('su')
      ->from(StudioUser::class, 'su')
      ->where('su.studio = :studio')
      ->andWhere('su.status = :status')
      ->setParameter('studio', $studio)
      ->setParameter('status', StudioUser::STATUS_ACTIVE)
      ->orderBy('su.created_on', 'ASC')
      ->addOrderBy('su.id', 'ASC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('su.id > :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var StudioUser[] $members */
    $members = $qb->getQuery()->getResult();

    $has_more = count($members) > $limit;
    if ($has_more) {
      array_pop($members);
    }

    return [
      'members' => $members,
      'has_more' => $has_more,
    ];
  }

  /**
   * @return array{projects: StudioProgram[], has_more: bool}
   */
  public function loadProjectsPage(Studio $studio, int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('sp')
      ->from(StudioProgram::class, 'sp')
      ->where('sp.studio = :studio')
      ->setParameter('studio', $studio)
      ->orderBy('sp.created_on', 'DESC')
      ->addOrderBy('sp.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('sp.id < :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var StudioProgram[] $projects */
    $projects = $qb->getQuery()->getResult();

    $has_more = count($projects) > $limit;
    if ($has_more) {
      array_pop($projects);
    }

    return [
      'projects' => $projects,
      'has_more' => $has_more,
    ];
  }

  /**
   * @return array{studios: Studio[], total: int, has_more: bool}
   */
  public function loadUserStudiosPage(User $user, int $limit, ?string $cursor_id, bool $include_private = false): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('s')
      ->from(Studio::class, 's')
      ->join(StudioUser::class, 'su', 'WITH', 'su.studio = s')
      ->where('su.user = :user')
      ->andWhere('su.status = :status')
      ->andWhere('s.auto_hidden = false')
      ->andWhere('s.is_enabled = true')
      ->setParameter('user', $user)
      ->setParameter('status', StudioUser::STATUS_ACTIVE)
    ;

    if (!$include_private) {
      $qb->andWhere('s.is_public = true');
    }

    $qb->orderBy('su.created_on', 'DESC')
      ->addOrderBy('su.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('su.id < :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var Studio[] $studios */
    $studios = $qb->getQuery()->getResult();

    $has_more = count($studios) > $limit;
    if ($has_more) {
      array_pop($studios);
    }

    $totalQb = $this->entity_manager->createQueryBuilder();
    $totalQb->select('COUNT(su.id)')
      ->from(StudioUser::class, 'su')
      ->join('su.studio', 's')
      ->where('su.user = :user')
      ->andWhere('su.status = :status')
      ->andWhere('s.auto_hidden = false')
      ->andWhere('s.is_enabled = true')
      ->setParameter('user', $user)
      ->setParameter('status', StudioUser::STATUS_ACTIVE)
    ;

    if (!$include_private) {
      $totalQb->andWhere('s.is_public = true');
    }

    $total = (int) $totalQb->getQuery()->getSingleScalarResult();

    return [
      'studios' => $studios,
      'total' => $total,
      'has_more' => $has_more,
    ];
  }

  /**
   * Returns the StudioUser ID for the last studio in the list (used for cursor pagination).
   */
  public function getStudioUserIdForCursor(User $user, Studio $studio): ?int
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('su.id')
      ->from(StudioUser::class, 'su')
      ->where('su.user = :user')
      ->andWhere('su.studio = :studio')
      ->andWhere('su.status = :status')
      ->setParameter('user', $user)
      ->setParameter('studio', $studio)
      ->setParameter('status', StudioUser::STATUS_ACTIVE)
      ->setMaxResults(1)
    ;

    $result = $qb->getQuery()->getOneOrNullResult();

    return $result ? (string) $result['id'] : null;
  }

  public function loadUserById(string $id): ?User
  {
    return $this->user_manager->findOneBy(['id' => $id]);
  }

  /**
   * @return array{activities: StudioActivity[], has_more: bool}
   */
  public function loadActivitiesPage(Studio $studio, int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('a')
      ->from(StudioActivity::class, 'a')
      ->where('a.studio = :studio')
      ->setParameter('studio', $studio)
      ->orderBy('a.created_on', 'DESC')
      ->addOrderBy('a.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('a.id < :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var StudioActivity[] $activities */
    $activities = $qb->getQuery()->getResult();

    $has_more = count($activities) > $limit;
    if ($has_more) {
      array_pop($activities);
    }

    return [
      'activities' => $activities,
      'has_more' => $has_more,
    ];
  }

  /**
   * @return list<array{id: string|null, name: string, in_studio: bool, screenshot_small: string|null}>
   */
  public function loadUserProjectsWithStudioFlag(User $user, Studio $studio): array
  {
    $user_projects = $this->studio_manager->getUserProjects($user);

    // Load all studio project IDs for this studio in one query to avoid N+1
    $qb = $this->entity_manager->createQueryBuilder();
    $studioProjectIds = $qb->select('IDENTITY(sp.program)')
      ->from(StudioProgram::class, 'sp')
      ->where('sp.studio = :studio')
      ->setParameter('studio', $studio)
      ->getQuery()
      ->getSingleColumnResult()
    ;

    $projects = [];
    foreach ($user_projects as $project) {
      $projectId = $project->getId();
      $projects[] = [
        'id' => $projectId,
        'name' => $project->getName(),
        'in_studio' => in_array($projectId, $studioProjectIds, true),
        'screenshot_small' => null !== $projectId ? '/'.$this->screenshot_repository->getThumbnailWebPath($projectId) : null,
      ];
    }

    return $projects;
  }

  /**
   * @return array{join_requests: StudioJoinRequest[], has_more: bool}
   */
  public function loadPendingJoinRequestsPage(Studio $studio, int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('jr')
      ->from(StudioJoinRequest::class, 'jr')
      ->where('jr.studio = :studio')
      ->andWhere('jr.status = :status')
      ->setParameter('studio', $studio)
      ->setParameter('status', StudioJoinRequest::STATUS_PENDING)
      ->orderBy('jr.id', 'ASC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('jr.id > :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var StudioJoinRequest[] $joinRequests */
    $joinRequests = $qb->getQuery()->getResult();

    $has_more = count($joinRequests) > $limit;
    if ($has_more) {
      array_pop($joinRequests);
    }

    return [
      'join_requests' => $joinRequests,
      'has_more' => $has_more,
    ];
  }

  public function loadJoinRequestById(string $id): ?StudioJoinRequest
  {
    return $this->studio_manager->findJoinRequestById($id);
  }

  public function loadStudioComment(string $comment_id): ?UserComment
  {
    return $this->studio_manager->findStudioCommentById($comment_id);
  }

  /**
   * @return array{comments: UserComment[], has_more: bool}
   */
  public function loadCommentsPage(Studio $studio, int $limit, ?string $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('c')
      ->from(UserComment::class, 'c')
      ->where('c.studio = :studio')
      ->setParameter('studio', $studio)
      ->orderBy('c.uploadDate', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      $qb->andWhere('c.id < :cursor_id')
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    /** @var UserComment[] $comments */
    $comments = $qb->getQuery()->getResult();

    $has_more = count($comments) > $limit;
    if ($has_more) {
      array_pop($comments);
    }

    return [
      'comments' => $comments,
      'has_more' => $has_more,
    ];
  }
}
