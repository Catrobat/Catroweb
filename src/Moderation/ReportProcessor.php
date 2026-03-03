<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Moderation\ContentReport;
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
    $this->checkRateLimit($reporter);

    $trust_score = $this->validateReport($reporter, $content_type, $content_id, $category);

    $effective_weight = $this->isPrivilegedReporter($reporter)
        ? $trust_score
        : min($trust_score, self::MAX_REPORT_WEIGHT);

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

  public function resolveReport(ContentReport $report, User $admin, string $action): void
  {
    $state = match ($action) {
      'accept' => ReportState::Accepted,
      'reject' => ReportState::Rejected,
      default => throw new \InvalidArgumentException('Invalid action: '.$action),
    };

    $content_type = ContentType::from($report->getContentType());
    $accepted = ReportState::Accepted === $state;
    $now = TimeUtils::getDateTime();

    // Resolve the primary report
    $report->setState($state->value);
    $report->setResolvedAt($now);
    $report->setResolvedBy($admin);

    if ($accepted) {
      $this->visibility_manager->hideContent($content_type, $report->getContentId());
    } else {
      // Only restore content visibility if no other active (new/accepted) reports exist.
      // This prevents un-hiding content that was legitimately reported by others.
      $has_other_reports = $this->report_repository->hasOtherActiveReports(
        $report->getContentType(),
        $report->getContentId(),
        $report->getId(),
      );
      if (!$has_other_reports) {
        $this->visibility_manager->showContent($content_type, $report->getContentId());
      }
    }

    $this->resolveRelatedReports($report, $state, $admin, $now);

    $audit = new ContentModerationAction();
    $audit->setContentType($report->getContentType());
    $audit->setContentId($report->getContentId());
    $audit->setAction($accepted ? ContentModerationAction::ACTION_ADMIN_CONFIRMED : ContentModerationAction::ACTION_ADMIN_REVERSED);
    $audit->setPerformedBy($admin);
    $audit->setNote('Report #'.$report->getId().' '.$action.'ed via admin');
    $this->entity_manager->persist($audit);

    if (null !== $report->getReporter()) {
      $this->trust_calculator->invalidate($report->getReporter());
    }

    $this->entity_manager->flush();
  }

  /**
   * @throws ReportException
   */
  private function isPrivilegedReporter(User $reporter): bool
  {
    return $reporter->hasRole('ROLE_ADMIN')
      || $reporter->hasRole('ROLE_SUPER_ADMIN')
      || $reporter->hasRole('ROLE_MODERATOR');
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

  private function resolveRelatedReports(ContentReport $primary, ReportState $state, User $admin, \DateTime $now): void
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
