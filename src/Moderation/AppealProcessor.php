<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Notifications\ModerationNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentAppealRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\AppealState;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AppealProcessor
{
  public function __construct(
    private readonly ContentAppealRepository $appeal_repository,
    private readonly ContentReportRepository $report_repository,
    private readonly ContentVisibilityManager $visibility_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly TrustScoreCalculator $trust_calculator,
  ) {
  }

  /**
   * @throws AppealException
   */
  public function processAppeal(
    User $appellant,
    ContentType $content_type,
    string $content_id,
    string $reason,
  ): ContentAppeal {
    $reason = trim($reason);
    if ('' === $reason) {
      throw AppealException::reasonRequired();
    }

    if (mb_strlen($reason) > ReportProcessor::MAX_NOTE_LENGTH) {
      $reason = mb_substr($reason, 0, ReportProcessor::MAX_NOTE_LENGTH);
    }

    $this->validateAppeal($appellant, $content_type, $content_id);

    // Remove any resolved (approved/rejected) appeals for the same content+user
    // to make room in the unique constraint for the new appeal.
    $this->appeal_repository->removeResolvedAppeals($content_type->value, $content_id, $appellant->getId());

    $appeal = new ContentAppeal();
    $appeal->setContentType($content_type->value);
    $appeal->setContentId($content_id);
    $appeal->setAppellant($appellant);
    $appeal->setReason($reason);

    $action = new ContentModerationAction();
    $action->setContentType($content_type->value);
    $action->setContentId($content_id);
    $action->setAction(ContentModerationAction::ACTION_APPEALED);
    $action->setPerformedBy($appellant);

    $this->entity_manager->persist($appeal);
    $this->entity_manager->persist($action);

    try {
      $this->entity_manager->flush();
    } catch (UniqueConstraintViolationException) {
      $this->entity_manager->detach($appeal);
      $this->entity_manager->detach($action);
      throw AppealException::appealAlreadyExists();
    }

    return $appeal;
  }

  public function approveAppeal(ContentAppeal $appeal, User $admin, ?string $note = null): void
  {
    $this->resolveAppeal($appeal, $admin, AppealState::Approved, $note);

    $content_type = ContentType::from($appeal->getContentType());
    $this->visibility_manager->showContent($content_type, $appeal->getContentId());

    // Only reject pending (New) reports — leave already-accepted reports as-is to avoid
    // retroactively penalizing reporters who made valid reports before the appeal.
    $reports = $this->report_repository->findReportsForContent($appeal->getContentType(), $appeal->getContentId());
    foreach ($reports as $report) {
      if (ReportState::New->value === $report->getState()) {
        $report->setState(ReportState::Rejected->value);
        $report->setResolvedAt(TimeUtils::getDateTime());
        $report->setResolvedBy($admin);
        if (null !== $report->getReporter()) {
          $this->trust_calculator->invalidate($report->getReporter());
        }
      }
    }

    $this->entity_manager->flush();

    if (null !== $appeal->getAppellant()) {
      $this->trust_calculator->invalidate($appeal->getAppellant());
    }
  }

  public function rejectAppeal(ContentAppeal $appeal, User $admin, ?string $note = null): void
  {
    $this->resolveAppeal($appeal, $admin, AppealState::Rejected, $note);

    $this->entity_manager->flush();
  }

  private function resolveAppeal(ContentAppeal $appeal, User $admin, AppealState $state, ?string $note): void
  {
    $appeal->setState($state->value);
    $appeal->setResolvedAt(TimeUtils::getDateTime());
    $appeal->setResolvedBy($admin);
    $appeal->setResolutionNote($note);

    $audit_action = AppealState::Approved === $state
      ? ContentModerationAction::ACTION_APPEAL_APPROVED
      : ContentModerationAction::ACTION_APPEAL_REJECTED;

    $action = new ContentModerationAction();
    $action->setContentType($appeal->getContentType());
    $action->setContentId($appeal->getContentId());
    $action->setAction($audit_action);
    $action->setPerformedBy($admin);
    $action->setNote($note);

    $content_type = ContentType::from($appeal->getContentType());
    $content_name = $this->visibility_manager->getContentName($content_type, $appeal->getContentId());
    $content_label = $content_name ? $content_type->value.' "'.$content_name.'"' : $content_type->value;

    $message = AppealState::Approved === $state
      ? 'Your appeal for '.$content_label.' has been approved. Your content is now visible again.'
      : 'Your appeal for '.$content_label.' has been rejected.'.($note ? ' Reason: '.$note : '');

    $notification = new ModerationNotification(
      $appeal->getAppellant(),
      $appeal->getContentType(),
      $appeal->getContentId(),
      $audit_action,
      $message,
    );

    $this->entity_manager->persist($action);
    $this->entity_manager->persist($notification);
  }

  /**
   * @throws AppealException
   */
  private function validateAppeal(User $appellant, ContentType $content_type, string $content_id): void
  {
    if (!$this->visibility_manager->contentExists($content_type, $content_id)) {
      throw AppealException::contentNotFound();
    }

    if (!$this->visibility_manager->isContentHidden($content_type, $content_id)) {
      throw AppealException::contentNotHidden();
    }

    $owner_id = $this->visibility_manager->getContentOwnerId($content_type, $content_id);
    if (null !== $owner_id && $owner_id !== $appellant->getId()) {
      throw AppealException::notOwner();
    }

    // For studios without an admin, require the appellant to be an active member
    if (ContentType::Studio === $content_type && null === $owner_id) {
      $studio = $this->entity_manager->find(Studio::class, $content_id);
      if (null === $studio) {
        throw AppealException::contentNotFound();
      }
      $membership = $this->entity_manager->getRepository(StudioUser::class)->findOneBy([
        'studio' => $studio,
        'user' => $appellant,
        'status' => StudioUser::STATUS_ACTIVE,
      ]);
      if (null === $membership) {
        throw AppealException::notOwner();
      }
    }

    if ($this->appeal_repository->hasExistingAppeal($content_type->value, $content_id, $appellant->getId())) {
      throw AppealException::appealAlreadyExists();
    }
  }
}
