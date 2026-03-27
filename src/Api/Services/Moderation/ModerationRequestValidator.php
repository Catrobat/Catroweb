<?php

declare(strict_types=1);

namespace App\Api\Services\Moderation;

use App\Api\Services\Base\AbstractRequestValidator;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Enum\AppealState;
use App\DB\Enum\ReportState;

class ModerationRequestValidator extends AbstractRequestValidator
{
  public const string ACTION_REPORT_ACCEPT = 'accept';
  public const string ACTION_REPORT_REJECT = 'reject';
  public const string ACTION_APPEAL_APPROVE = 'approve';
  public const string ACTION_APPEAL_REJECT = 'reject';

  private const array VALID_REPORT_ACTIONS = [self::ACTION_REPORT_ACCEPT, self::ACTION_REPORT_REJECT];
  private const array VALID_APPEAL_ACTIONS = [self::ACTION_APPEAL_APPROVE, self::ACTION_APPEAL_REJECT];

  public function isValidReportResolveAction(string $action): bool
  {
    return in_array($action, self::VALID_REPORT_ACTIONS, true);
  }

  public function isValidAppealResolveAction(string $action): bool
  {
    return in_array($action, self::VALID_APPEAL_ACTIONS, true);
  }

  public function isReportPending(ContentReport $report): bool
  {
    return ReportState::New->value === $report->getState();
  }

  public function isAppealPending(ContentAppeal $appeal): bool
  {
    return AppealState::Pending->value === $appeal->getState();
  }
}
