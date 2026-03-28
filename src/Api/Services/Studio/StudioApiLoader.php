<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;

class StudioApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly StudioManager $studio_manager,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function loadStudioByID(string $id): ?Studio
  {
    return $this->studio_manager->findStudioById($id);
  }

  public function loadVisibleStudio(string $id): ?Studio
  {
    $studio = $this->studio_manager->findStudioById($id);

    if (!$studio instanceof Studio || $studio->getAutoHidden()) {
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
  public function loadStudiosPage(int $limit, ?int $cursor_id): array
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('s')
      ->from(Studio::class, 's')
      ->where('s.auto_hidden = false')
      ->andWhere('s.is_public = true')
      ->orderBy('s.created_on', 'DESC')
      ->addOrderBy('s.id', 'DESC')
      ->setMaxResults($limit + 1)
    ;

    if (null !== $cursor_id) {
      // cursor_id is a studio_user id used for offset — but studios use UUID.
      // For studios we use a simple offset-based cursor: the encoded ID is the row number
      // Actually, for UUID-based entities, we encode the created_on timestamp.
      // Simplest approach: use offset-based with cursor = base64(offset_number)
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
  public function loadMembersPage(Studio $studio, int $limit, ?int $cursor_id): array
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
  public function loadProjectsPage(Studio $studio, int $limit, ?int $cursor_id): array
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
   * @return array{comments: UserComment[], has_more: bool}
   */
  public function loadCommentsPage(Studio $studio, int $limit, ?int $cursor_id): array
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
