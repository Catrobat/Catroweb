<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\DB\Enum\ReportState;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class ReportProcessor
{
  private const float AUTO_HIDE_THRESHOLD = 10.0;
  private const float MIN_TRUST_TO_REPORT = 0.5;
  private const float MAX_REPORT_WEIGHT = 5.0;
  private const int VELOCITY_REPORTER_THRESHOLD = 5;
  private const int VELOCITY_WINDOW_MINUTES = 30;
  public const int MAX_NOTE_LENGTH = 5000;

  /** Hide the content and ban (profile-hide) its owner. */
  public const string ACTION_ACCEPT = 'accept';
  public const string ACTION_REJECT = 'reject';
  /** Hide the content, warn the owner, no ban. For user reports: clear the profile texts instead of hiding. */
  public const string ACTION_HIDE_WARN = 'hide_warn';
  /** Keep the content visible, warn the owner. */
  public const string ACTION_WARN = 'warn';
  public const array ACTIONS = [self::ACTION_ACCEPT, self::ACTION_REJECT, self::ACTION_HIDE_WARN, self::ACTION_WARN];
  /** ModerationNotification::moderation_action of an owner warning. */
  public const string NOTIFICATION_ADMIN_WARNING = 'admin_warning';
  /** ModerationNotification::moderation_action of an owner warning whose content was hidden (hide_warn). */
  public const string NOTIFICATION_ADMIN_HIDE_WARNING = 'admin_hide_warning';

  public function __construct(
    private readonly ContentReportRepository $report_repository,
    private readonly TrustScoreCalculator $trust_calculator,
    private readonly AutoModerationService $auto_moderation,
    private readonly ContentVisibilityManager $visibility_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly RateLimiterFactoryInterface $reportBurstLimiter,
    private readonly RateLimiterFactoryInterface $reportDailyLimiter,
  ) {
  }

  /**
   * @return array{report: ContentReport, auto_hidden: bool}
   *
   * @throws ReportException
   */
  public function processReport(
    User $reporter,
    ContentType $content_type,
    string $content_id,
    string $category,
    ?string $note = null,
  ): array {
    $trust_score = $this->validateReport($reporter, $content_type, $content_id, $category);

    $this->checkRateLimit($reporter);

    $effective_weight = $this->isPrivilegedReporter($reporter)
        ? $trust_score
        : min($trust_score, self::MAX_REPORT_WEIGHT);

    if (null !== $note && mb_strlen($note) > self::MAX_NOTE_LENGTH) {
      $note = mb_substr($note, 0, self::MAX_NOTE_LENGTH);
    }

    $report = new ContentReport();
    $report->setReporter($reporter);
    $report->setContentType($content_type->value);
    $report->setContentId($content_id);
    $report->setCategory($category);
    $report->setNote($note);
    $report->setReporterTrustScore($effective_weight);

    $this->entity_manager->persist($report);

    try {
      $this->entity_manager->flush();
    } catch (UniqueConstraintViolationException) {
      $this->entity_manager->detach($report);
      throw ReportException::duplicateReport();
    }

    $cumulative_score = $this->report_repository->getCumulativeTrustScore(
      $content_type->value,
      $content_id
    );

    $auto_hidden = false;
    if ($cumulative_score >= self::AUTO_HIDE_THRESHOLD
        && !$this->visibility_manager->isContentHidden($content_type, $content_id)
        && !$this->visibility_manager->isWhitelisted($content_type, $content_id)) {
      $velocity = $this->report_repository->getRecentReportVelocity(
        $content_type->value,
        $content_id,
        self::VELOCITY_WINDOW_MINUTES
      );

      if ($velocity >= self::VELOCITY_REPORTER_THRESHOLD) {
        $this->auto_moderation->notifyAdminsOfSuspectedBrigading(
          $content_type,
          $content_id,
          $cumulative_score,
          $velocity
        );
      } else {
        $this->auto_moderation->autoHideContent($content_type, $content_id, $cumulative_score);
        $auto_hidden = true;
      }
    }

    return ['report' => $report, 'auto_hidden' => $auto_hidden];
  }

  /**
   * @param string      $action  one of the ACTION_* constants
   * @param string|null $message warning text for the owner (hide_warn/warn); empty = translated default at render time
   */
  public function resolveReport(ContentReport $report, User $admin, string $action, ?string $message = null): void
  {
    $audit_action = match ($action) {
      self::ACTION_ACCEPT => ContentModerationAction::ACTION_ADMIN_CONFIRMED,
      self::ACTION_REJECT => ContentModerationAction::ACTION_ADMIN_REVERSED,
      // A reported user is not hidden by hide_warn (profile texts are cleared), so that is a plain warning.
      self::ACTION_HIDE_WARN => ContentType::User->value === $report->getContentType() ? ContentModerationAction::ACTION_ADMIN_WARNED : ContentModerationAction::ACTION_ADMIN_HIDE_WARNED,
      self::ACTION_WARN => ContentModerationAction::ACTION_ADMIN_WARNED,
      default => throw new \InvalidArgumentException('Invalid action: '.$action),
    };
    $state = self::ACTION_REJECT === $action ? ReportState::Rejected : ReportState::Accepted;

    $content_type = ContentType::from($report->getContentType());
    $content_id = $report->getContentId();
    $now = TimeUtils::getDateTime();

    // Resolve the primary report
    $report->setState($state->value);
    $report->setResolvedAt($now);
    $report->setResolvedBy($admin);

    switch ($action) {
      case self::ACTION_ACCEPT:
        $this->visibility_manager->hideContent($content_type, $content_id);
        $this->banContentOwner($content_type, $content_id, $admin);
        break;
      case self::ACTION_HIDE_WARN:
        $this->hideContentWithoutBan($content_type, $content_id);
        $this->warnContentOwner($content_type, $content_id, $message, ContentType::User === $content_type ? self::NOTIFICATION_ADMIN_WARNING : self::NOTIFICATION_ADMIN_HIDE_WARNING);
        break;
      case self::ACTION_WARN:
        // Visibility is left as is: un-hiding here could lift an earlier ban or accepted report.
        $this->warnContentOwner($content_type, $content_id, $message);
        break;
      default:
        // Only restore content visibility if no other active (new/accepted) reports exist.
        // This prevents un-hiding content that was legitimately reported by others.
        $report_id = $report->getId();
        $has_other_reports = null !== $report_id && $this->report_repository->hasOtherActiveReports(
          $report->getContentType(),
          $content_id,
          $report_id,
        );
        if (!$has_other_reports) {
          $this->visibility_manager->showContent($content_type, $content_id);
        }
    }

    $all_reports = $this->resolveRelatedReports($report, $state, $admin, $now);

    $audit = new ContentModerationAction();
    $audit->setContentType($report->getContentType());
    $audit->setContentId($content_id);
    $audit->setAction($audit_action);
    $audit->setPerformedBy($admin);
    $audit->setNote('Report #'.$report->getId().' resolved via admin: '.$action);
    $this->entity_manager->persist($audit);

    if ($report->getReporter() instanceof User) {
      $this->trust_calculator->invalidate($report->getReporter());
    }

    $this->notifyReporters($report, $all_reports, $state, $content_type);

    $this->entity_manager->flush();
  }

  /**
   * @throws ReportException
   */
  private function isPrivilegedReporter(User $reporter): bool
  {
    if ($reporter->hasRole('ROLE_ADMIN')) {
      return true;
    }
    if ($reporter->hasRole('ROLE_SUPER_ADMIN')) {
      return true;
    }

    return $reporter->hasRole('ROLE_MODERATOR');
  }

  private function checkRateLimit(User $reporter): void
  {
    if ($this->isPrivilegedReporter($reporter)) {
      return;
    }

    $reporter_id = $reporter->getId();

    $burst = $this->reportBurstLimiter->create($reporter_id);
    if (!$burst->consume(1)->isAccepted()) {
      throw ReportException::rateLimited();
    }

    $daily = $this->reportDailyLimiter->create($reporter_id);
    if (!$daily->consume(1)->isAccepted()) {
      throw ReportException::rateLimited();
    }
  }

  /**
   * @return ContentReport[]
   */
  private function resolveRelatedReports(ContentReport $primary, ReportState $state, User $admin, \DateTime $now): array
  {
    $related_reports = $this->report_repository->findReportsForContent(
      $primary->getContentType(),
      $primary->getContentId()
    );

    foreach ($related_reports as $related) {
      if ($related->getId() === $primary->getId()) {
        continue;
      }
      if (ReportState::New->value !== $related->getState()) {
        continue;
      }
      $related->setState($state->value);
      $related->setResolvedAt($now);
      $related->setResolvedBy($admin);
      if (null !== $related->getReporter()) {
        $this->trust_calculator->invalidate($related->getReporter());
      }
    }

    return $related_reports;
  }

  /**
   * @param ContentReport[] $all_reports
   */
  private function notifyReporters(ContentReport $primary_report, array $all_reports, ReportState $state, ContentType $content_type): void
  {
    $notified_users = [];
    $action = ReportState::Accepted === $state ? 'report_accepted' : 'report_rejected';
    $message = ReportState::Accepted === $state
      ? 'Your report regarding a '.$content_type->value.' has been reviewed and accepted.'
      : 'Your report regarding a '.$content_type->value.' has been reviewed and rejected.';

    foreach ($all_reports as $report) {
      $reporter = $report->getReporter();
      if (!$reporter instanceof User) {
        continue;
      }

      $reporter_id = $reporter->getId();
      if (isset($notified_users[$reporter_id])) {
        continue;
      }
      $notified_users[$reporter_id] = true;

      $notification = new ModerationNotification(
        $reporter,
        $content_type->value,
        $primary_report->getContentId(),
        $action,
        $message,
      );
      $this->entity_manager->persist($notification);
    }
  }

  /**
   * When an admin accepts a report on a project, studio, or comment,
   * also ban (profile-hide) the content owner as a safety measure.
   */
  private function banContentOwner(ContentType $content_type, string $content_id, User $admin): void
  {
    if (ContentType::User === $content_type) {
      return; // User reports already ban the user directly via hideContent()
    }

    $owner_id = $this->visibility_manager->getContentOwnerId($content_type, $content_id);
    if (null === $owner_id) {
      return;
    }

    $owner = $this->entity_manager->find(User::class, $owner_id);
    if (!$owner instanceof User) {
      return;
    }

    // An admin ban, even of an account the community already hid: catro:moderation:purge-hidden relies on it.
    $audit = new ContentModerationAction();
    $audit->setContentType(ContentType::User->value);
    $audit->setContentId($owner_id);
    $audit->setAction(ContentModerationAction::ACTION_ADMIN_BANNED);
    $audit->setPerformedBy($admin);
    $audit->setNote('Owner of '.$content_type->value.' '.$content_id);
    $this->entity_manager->persist($audit);

    if ($owner->getProfileHidden()) {
      return; // Already banned
    }

    $this->visibility_manager->hideContent(ContentType::User, $owner_id);
  }

  /**
   * Like hideContent(), but a reported user is not banned: their profile texts are cleared instead.
   */
  private function hideContentWithoutBan(ContentType $content_type, string $content_id): void
  {
    if (ContentType::User !== $content_type) {
      $this->visibility_manager->hideContent($content_type, $content_id);

      return;
    }

    $user = $this->entity_manager->find(User::class, $content_id);
    if ($user instanceof User) {
      $user->setAbout(null);
      $user->setCurrentlyWorkingOn(null);
    }
  }

  private function warnContentOwner(ContentType $content_type, string $content_id, ?string $message, string $notification_action = self::NOTIFICATION_ADMIN_WARNING): void
  {
    $owner_id = $this->visibility_manager->getContentOwnerId($content_type, $content_id);
    $owner = null === $owner_id ? null : $this->entity_manager->find(User::class, $owner_id);
    if (!$owner instanceof User) {
      return;
    }

    $this->entity_manager->persist(new ModerationNotification(
      $owner,
      $content_type->value,
      $content_id,
      $notification_action,
      mb_substr(trim($message ?? ''), 0, self::MAX_NOTE_LENGTH),
    ));
  }

  /**
   * @return float The reporter's trust score (avoids duplicate calculation)
   *
   * @throws ReportException
   */
  private function validateReport(
    User $reporter,
    ContentType $content_type,
    string $content_id,
    string $category,
  ): float {
    if (!$this->visibility_manager->contentExists($content_type, $content_id)) {
      throw ReportException::contentNotFound();
    }

    if ($this->visibility_manager->isContentHidden($content_type, $content_id)) {
      throw ReportException::contentAlreadyHidden();
    }

    if ($this->visibility_manager->isWhitelisted($content_type, $content_id)) {
      throw ReportException::contentWhitelisted();
    }

    if (!$reporter->isVerified()) {
      throw ReportException::emailNotVerified();
    }

    if (!ReportCategory::isValidForContentType($category, $content_type)) {
      throw ReportException::invalidCategory($category, $content_type);
    }

    $trust_score = $this->trust_calculator->calculate($reporter);
    if ($trust_score < self::MIN_TRUST_TO_REPORT) {
      throw ReportException::trustTooLow($trust_score, self::MIN_TRUST_TO_REPORT);
    }

    $owner_id = $this->visibility_manager->getContentOwnerId($content_type, $content_id);
    if (null !== $owner_id && $owner_id === $reporter->getId()) {
      throw ReportException::cannotReportOwnContent();
    }

    if ($this->report_repository->hasUserAlreadyReported($reporter->getId(), $content_type->value, $content_id)) {
      throw ReportException::duplicateReport();
    }

    return $trust_score;
  }
}
