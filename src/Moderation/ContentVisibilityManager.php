<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use Doctrine\ORM\EntityManagerInterface;

class ContentVisibilityManager
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function hideContent(ContentType $content_type, string $content_id): void
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return;
    }

    match ($content_type) {
      ContentType::Project => $entity->setAutoHidden(true),
      ContentType::Comment => $entity->setAutoHidden(true),
      ContentType::User => $entity->setProfileHidden(true),
      ContentType::Studio => $entity->setAutoHidden(true),
    };

    if (ContentType::User === $content_type) {
      $this->hideAllUserContent($entity);
    }
  }

  public function showContent(ContentType $content_type, string $content_id): void
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return;
    }

    // For non-User content, skip restore if the content owner is suspended.
    // This prevents unhiding content belonging to a banned user via report rejection or appeal approval.
    if (ContentType::User !== $content_type) {
      $owner_id = $this->getContentOwnerId($content_type, $content_id);
      if (null !== $owner_id) {
        $owner = $this->entity_manager->find(User::class, $owner_id);
        if ($owner instanceof User && $owner->getProfileHidden()) {
          return;
        }
      }
    }

    match ($content_type) {
      ContentType::Project => $entity->setAutoHidden(false),
      ContentType::Comment => $entity->setAutoHidden(false),
      ContentType::User => $entity->setProfileHidden(false),
      ContentType::Studio => $entity->setAutoHidden(false),
    };

    if (ContentType::User === $content_type) {
      $this->restoreUserContentNotIndependentlyReported($entity);
    }
  }

  public function isContentHidden(ContentType $content_type, string $content_id): bool
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return false;
    }

    return match ($content_type) {
      ContentType::Project => $entity->getAutoHidden(),
      ContentType::Comment => $entity->getAutoHidden(),
      ContentType::User => $entity->getProfileHidden(),
      ContentType::Studio => $entity->getAutoHidden(),
    };
  }

  public function getContentOwnerId(ContentType $content_type, string $content_id): ?string
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return null;
    }

    return match ($content_type) {
      ContentType::Project => $entity->getUser()?->getId(),
      ContentType::Comment => $entity->getUser()?->getId(),
      ContentType::User => $entity->getId(),
      ContentType::Studio => $this->entity_manager
        ->getRepository(StudioUser::class)
        ->findOneBy(
          [
            'studio' => $entity,
            'role' => StudioUser::ROLE_ADMIN,
            'status' => StudioUser::STATUS_ACTIVE,
          ],
          ['created_on' => 'ASC']
        )
        ?->getUser()
        ?->getId(),
    };
  }

  public function getContentName(ContentType $content_type, string $content_id): ?string
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return null;
    }

    return match ($content_type) {
      ContentType::Project => $entity->getName(),
      ContentType::Comment => mb_substr((string) $entity->getText(), 0, 50).(mb_strlen((string) $entity->getText()) > 50 ? '...' : ''),
      ContentType::User => $entity->getUserIdentifier(),
      ContentType::Studio => $entity->getName(),
    };
  }

  /**
   * For comment content, returns the associated project ID.
   */
  public function getCommentProjectId(string $content_id): ?string
  {
    $comment = $this->entity_manager->find(UserComment::class, (int) $content_id);

    return $comment?->getProgram()?->getId();
  }

  /**
   * For comment content, returns the associated project name.
   */
  public function getCommentProjectName(string $content_id): ?string
  {
    $comment = $this->entity_manager->find(UserComment::class, (int) $content_id);

    return $comment?->getProgram()?->getName();
  }

  public function isWhitelisted(ContentType $content_type, string $content_id): bool
  {
    $entity = $this->findEntity($content_type, $content_id);
    if (null === $entity) {
      return false;
    }

    return match ($content_type) {
      ContentType::Project => $entity->getApproved() || ($entity->getUser()?->isApproved() ?? false),
      ContentType::Comment => $entity->getUser()?->isApproved() ?? false,
      ContentType::User => $entity->isApproved(),
      ContentType::Studio => false,
    };
  }

  public function contentExists(ContentType $content_type, string $content_id): bool
  {
    return null !== $this->findEntity($content_type, $content_id);
  }

  /**
   * Bulk-hides all projects and comments by a user (used during ban cascade).
   */
  private function hideAllUserContent(User $user): void
  {
    foreach ([Program::class, UserComment::class] as $entity_class) {
      $alias = Program::class === $entity_class ? 'p' : 'c';
      $this->entity_manager->createQueryBuilder()
        ->update($entity_class, $alias)
        ->set($alias.'.auto_hidden', ':target')
        ->where($alias.'.user = :user')
        ->andWhere($alias.'.auto_hidden = :current')
        ->setParameter('target', true)
        ->setParameter('current', false)
        ->setParameter('user', $user)
        ->getQuery()
        ->execute()
      ;
    }
  }

  /**
   * Restores auto_hidden content for a user, but skips content that has active
   * (new or accepted) community reports — those were independently hidden and
   * should not be restored when unbanning a user.
   */
  private function restoreUserContentNotIndependentlyReported(User $user): void
  {
    $content_type_map = [
      Program::class => ['alias' => 'p', 'content_type' => ContentType::Project->value],
      UserComment::class => ['alias' => 'c', 'content_type' => ContentType::Comment->value],
    ];

    foreach ($content_type_map as $entity_class => $config) {
      $alias = $config['alias'];

      // Get this user's hidden content IDs first, then check which have active reports.
      // This scopes the report query to only the user's content instead of all reported content globally.
      $user_content_ids = $this->entity_manager->createQueryBuilder()
        ->select($alias.'.id')
        ->from($entity_class, $alias)
        ->where($alias.'.user = :user')
        ->andWhere($alias.'.auto_hidden = :hidden')
        ->setParameter('user', $user)
        ->setParameter('hidden', true)
        ->getQuery()
        ->getSingleColumnResult()
      ;

      if ([] === $user_content_ids) {
        continue;
      }

      // For comments, content_id is stored as string in reports but entity id is int
      $string_ids = UserComment::class === $entity_class
        ? array_map(strval(...), $user_content_ids)
        : $user_content_ids;

      // Find which of this user's content has active reports (should stay hidden)
      $reported_ids = $this->entity_manager->createQueryBuilder()
        ->select('r.content_id')
        ->from(ContentReport::class, 'r')
        ->where('r.content_type = :content_type')
        ->andWhere('r.content_id IN (:user_content_ids)')
        ->andWhere('r.state IN (:active_states)')
        ->setParameter('content_type', $config['content_type'])
        ->setParameter('user_content_ids', $string_ids)
        ->setParameter('active_states', [ReportState::New->value, ReportState::Accepted->value])
        ->groupBy('r.content_id')
        ->getQuery()
        ->getSingleColumnResult()
      ;

      $qb = $this->entity_manager->createQueryBuilder()
        ->update($entity_class, $alias)
        ->set($alias.'.auto_hidden', ':target')
        ->where($alias.'.user = :user')
        ->andWhere($alias.'.auto_hidden = :current')
        ->setParameter('target', false)
        ->setParameter('current', true)
        ->setParameter('user', $user)
      ;

      if ([] !== $reported_ids) {
        $typed_ids = UserComment::class === $entity_class
          ? array_map(intval(...), $reported_ids)
          : $reported_ids;

        $qb->andWhere($qb->expr()->notIn($alias.'.id', ':reported_ids'))
          ->setParameter('reported_ids', $typed_ids)
        ;
      }

      $qb->getQuery()->execute();
    }
  }

  private function findEntity(ContentType $content_type, string $content_id): Program|UserComment|User|Studio|null
  {
    return match ($content_type) {
      ContentType::Project => $this->entity_manager->find(Program::class, $content_id),
      ContentType::Comment => $this->entity_manager->find(UserComment::class, (int) $content_id),
      ContentType::User => $this->entity_manager->find(User::class, $content_id),
      ContentType::Studio => $this->entity_manager->find(Studio::class, $content_id),
    };
  }
}
