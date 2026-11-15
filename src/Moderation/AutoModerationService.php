<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\DB\Enum\ReportState;
use App\Security\ContentSafety\ContentSafetyScanner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class AutoModerationService
{
  public function __construct(
    private readonly ContentVisibilityManager $visibility_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly AgeSignalDetector $age_signal_detector,
    private readonly ContentSafetyScanner $content_safety_scanner,
    private readonly MessageBusInterface $message_bus,
  ) {
  }

  /** Admin notification for content queued without hiding it (profiles). */
  public const string ACTION_REVIEW_REQUESTED = 'review_requested';

  /**
   * Hides a project or studio whose texts say it is for adults ("18+", "nsfw", …) until an admin reviews it.
   * The texts stay as they are. Users: flagUserProfile().
   */
  public function flagAgeSignal(ContentType $content_type, string $content_id, ?string ...$texts): bool
  {
    $signal = $this->age_signal_detector->findSignal(...$texts);
    if (null === $signal) {
      return false;
    }

    $category = match ($content_type) {
      ContentType::Project => ReportCategory::ImproperRating,
      ContentType::Studio => ReportCategory::InappropriateContent,
      ContentType::Comment => ReportCategory::Inappropriate,
      ContentType::User => throw new \LogicException('Accounts are never hidden automatically, use flagUserProfile()'),
    };
    $this->flagForReview($content_type, $content_id, $category, self::signalNote($signal));

    return true;
  }

  /**
   * Accounts stay usable: an about or "currently working on" text with an adults-only signal is cleared, a
   * username is kept, and the profile goes to the moderation queue with a notification to admins only.
   */
  public function flagUserProfile(User $user): bool
  {
    $signals = [];
    $signal = $this->age_signal_detector->findSignal($user->getAbout());
    if (null !== $signal) {
      $signals[] = $signal;
      $user->setAbout(null);
    }

    $signal = $this->age_signal_detector->findSignal($user->getCurrentlyWorkingOn());
    if (null !== $signal) {
      $signals[] = $signal;
      $user->setCurrentlyWorkingOn(null);
    }

    $cleared = [] !== $signals;
    $signal = $this->age_signal_detector->findSignal($user->getUsername());
    if (null !== $signal) {
      $signals[] = $signal;
    }

    if ([] === $signals) {
      return false;
    }

    $user_id = (string) $user->getId();
    $pending = $this->entity_manager->getRepository(ContentReport::class)->findOneBy([
      'content_type' => ContentType::User->value,
      'content_id' => $user_id,
      'reporter' => null,
      'state' => ReportState::New->value,
    ]);
    if (null === $pending) {
      $note = self::signalNote(implode('", "', $signals)).($cleared ? '; the profile texts with it were cleared' : ' in the username');
      $this->queueReport(ContentType::User, $user_id, ReportCategory::InappropriateProfile, $note);
      $this->notifyAdmins(ContentType::User, $user_id, 'matched an adults-only signal and needs review (the account stays usable)', self::ACTION_REVIEW_REQUESTED);
    }

    $this->entity_manager->flush();

    return true;
  }

  /**
   * Scans the content's images on the default worker (ScanContentImagesHandler), so uploads don't wait.
   * $delay_ms: an upload moves its extracted files into place only after its event listeners ran.
   */
  public function queueImageScan(ContentType $content_type, string $content_id, int $delay_ms = 0): void
  {
    if ($this->content_safety_scanner->isEnabled()) {
      $this->message_bus->dispatch(new ScanContentImages($content_type->value, $content_id), $delay_ms > 0 ? [new DelayStamp($delay_ms)] : []);
    }
  }

  /**
   * Hides the content until an admin reviews it: a report without reporter puts it in the moderation queue,
   * where accepting or rejecting it works like for a community report.
   */
  public function flagForReview(ContentType $content_type, string $content_id, ReportCategory $category, string $note, ?float $score = null): void
  {
    if (ContentType::User === $content_type) {
      throw new \LogicException('Accounts are never hidden automatically, use flagUserProfile()');
    }

    if ($this->visibility_manager->isContentHidden($content_type, $content_id)) {
      return;
    }

    $this->visibility_manager->hideContent($content_type, $content_id);

    $action = new ContentModerationAction();
    $action->setContentType($content_type->value);
    $action->setContentId($content_id);
    $action->setAction(ContentModerationAction::ACTION_AUTO_HIDDEN);
    $action->setCumulativeScore($score);
    $action->setNote($note);
    $this->entity_manager->persist($action);

    $this->queueReport($content_type, $content_id, $category, $note);
    $this->notifyContentOwner($content_type, $content_id, 'is hidden until a moderator has reviewed it');
    $this->notifyAdmins($content_type, $content_id);

    $this->entity_manager->flush();
  }

  private static function signalNote(string $signal): string
  {
    return sprintf('Automatic: adults-only signal "%s"', $signal);
  }

  private function queueReport(ContentType $content_type, string $content_id, ReportCategory $category, string $note): void
  {
    $report = new ContentReport();
    $report->setContentType($content_type->value);
    $report->setContentId($content_id);
    $report->setCategory($category->value);
    $report->setNote($note);
    $this->entity_manager->persist($report);
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

  private function notifyContentOwner(ContentType $content_type, string $content_id, string $reason = 'has been hidden due to community reports'): void
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
      ContentType::Project => 'Your project "%s" ',
      ContentType::Comment => 'Your comment "%s" ',
      ContentType::User => 'Your profile ',
      ContentType::Studio => 'Your studio "%s" ',
    }.$reason.'. You may appeal this decision.';

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

  private function notifyAdmins(ContentType $content_type, string $content_id, string $what = 'was auto-hidden and needs review', string $action = ContentModerationAction::ACTION_AUTO_HIDDEN): void
  {
    $admins = $this->findAdmins();
    $content_name = $this->visibility_manager->getContentName($content_type, $content_id);
    $label = $content_name ? $content_type->value.' "'.$content_name.'"' : $content_type->value.' #'.$content_id;

    foreach ($admins as $admin) {
      $notification = new ModerationNotification(
        $admin,
        $content_type->value,
        $content_id,
        $action,
        'Content ('.$label.') '.$what.'.',
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
