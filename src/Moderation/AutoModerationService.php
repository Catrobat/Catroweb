<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use Doctrine\ORM\EntityManagerInterface;

class AutoModerationService
{
  public function __construct(
    private readonly ContentVisibilityManager $visibility_manager,
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function autoHideContent(ContentType $content_type, string $content_id, float $cumulative_score): void
  {
    // Idempotency guard: if content is already hidden (e.g. by a concurrent request),
    // skip the audit entry and notifications to avoid duplicates.
    if ($this->visibility_manager->isContentHidden($content_type, $content_id)) {
      return;
    }

    $this->visibility_manager->hideContent($content_type, $content_id);
    $this->createAuditEntry($content_type, $content_id, $cumulative_score);
    $this->notifyContentOwner($content_type, $content_id);
    $this->notifyAdmins($content_type, $content_id);

    $this->entity_manager->flush();
  }

  private function createAuditEntry(ContentType $content_type, string $content_id, float $cumulative_score): void
  {
    $action = new ContentModerationAction();
    $action->setContentType($content_type->value);
    $action->setContentId($content_id);
    $action->setAction(ContentModerationAction::ACTION_AUTO_HIDDEN);
    $action->setCumulativeScore($cumulative_score);

    $this->entity_manager->persist($action);
  }

  private function notifyContentOwner(ContentType $content_type, string $content_id): void
  {
    $owner_id = $this->visibility_manager->getContentOwnerId($content_type, $content_id);
    if (null === $owner_id) {
      return;
    }

    $owner = $this->entity_manager->find(User::class, $owner_id);
    if (null === $owner) {
      return;
    }

    $content_name = $this->visibility_manager->getContentName($content_type, $content_id);
    $message = match ($content_type) {
      ContentType::Project => 'Your project "%s" has been hidden due to community reports. You may appeal this decision.',
      ContentType::Comment => 'Your comment "%s" has been hidden due to community reports. You may appeal this decision.',
      ContentType::User => 'Your profile has been hidden due to community reports. You may appeal this decision.',
      ContentType::Studio => 'Your studio "%s" has been hidden due to community reports. You may appeal this decision.',
    };

    $notification = new ModerationNotification(
      $owner,
      $content_type->value,
      $content_id,
      ContentModerationAction::ACTION_AUTO_HIDDEN,
      ContentType::User === $content_type ? $message : sprintf($message, $content_name ?? $content_id),
    );

    $this->entity_manager->persist($notification);
  }

  /**
   * @return User[]
   */
  private function findAdmins(): array
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->from(User::class, 'u')
      ->where('u.roles LIKE :role')
      ->setParameter('role', '%"ROLE_ADMIN"%')
      ->getQuery()
      ->getResult()
    ;
  }

  private function notifyAdmins(ContentType $content_type, string $content_id): void
  {
    $admins = $this->findAdmins();
    $content_name = $this->visibility_manager->getContentName($content_type, $content_id);
    $label = $content_name ? $content_type->value.' "'.$content_name.'"' : $content_type->value.' #'.$content_id;

    foreach ($admins as $admin) {
      $notification = new ModerationNotification(
        $admin,
        $content_type->value,
        $content_id,
        ContentModerationAction::ACTION_AUTO_HIDDEN,
        'Content ('.$label.') was auto-hidden and needs review.',
      );

      $this->entity_manager->persist($notification);
    }
  }

  public function notifyAdminsOfSuspectedBrigading(
    ContentType $content_type,
    string $content_id,
    float $cumulative_score,
    int $velocity,
  ): void {
    $action = new ContentModerationAction();
    $action->setContentType($content_type->value);
    $action->setContentId($content_id);
    $action->setAction(ContentModerationAction::ACTION_BRIGADING_SUSPECTED);
    $action->setCumulativeScore($cumulative_score);
    $action->setNote(sprintf(
      '%d distinct reporters in 30 minutes — possible coordinated reporting',
      $velocity
    ));
    $this->entity_manager->persist($action);

    $admins = $this->findAdmins();
    $content_name = $this->visibility_manager->getContentName($content_type, $content_id);
    $label = $content_name ? $content_type->value.' "'.$content_name.'"' : $content_type->value.' #'.$content_id;

    foreach ($admins as $admin) {
      $notification = new ModerationNotification(
        $admin,
        $content_type->value,
        $content_id,
        ContentModerationAction::ACTION_BRIGADING_SUSPECTED,
        sprintf(
          'Content (%s) received reports from %d distinct users in 30 minutes (score: %.1f). Possible coordinated reporting — manual review required.',
          $label,
          $velocity,
          $cumulative_score
        ),
      );
      $this->entity_manager->persist($notification);
    }

    $this->entity_manager->flush();
  }
}
