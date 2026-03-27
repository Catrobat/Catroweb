<?php

declare(strict_types=1);

namespace App\Api\Services\Moderation;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\Moderation\AppealException;
use App\Moderation\AppealProcessor;
use App\Moderation\ReportException;
use App\Moderation\ReportProcessor;
use OpenAPI\Server\Model\ContentAppealRequest;
use OpenAPI\Server\Model\ContentReportRequest;

class ModerationApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly ReportProcessor $report_processor,
    private readonly AppealProcessor $appeal_processor,
  ) {
  }

  /**
   * @throws ReportException
   */
  public function processReport(
    User $user,
    ContentType $content_type,
    string $content_id,
    ContentReportRequest $request,
  ): void {
    $this->report_processor->processReport(
      $user,
      $content_type,
      $content_id,
      $request->getCategory() ?? '',
      $request->getNote(),
    );
  }

  /**
   * @throws AppealException
   */
  public function processAppeal(
    User $user,
    ContentType $content_type,
    string $content_id,
    ContentAppealRequest $request,
  ): void {
    $this->appeal_processor->processAppeal(
      $user,
      $content_type,
      $content_id,
      $request->getReason() ?? '',
    );
  }

  public function resolveReport(ContentReport $report, User $admin, string $action): void
  {
    $this->report_processor->resolveReport($report, $admin, $action);
  }

  public function approveAppeal(ContentAppeal $appeal, User $admin, ?string $note): void
  {
    $this->appeal_processor->approveAppeal($appeal, $admin, $note);
  }

  public function rejectAppeal(ContentAppeal $appeal, User $admin, ?string $note): void
  {
    $this->appeal_processor->rejectAppeal($appeal, $admin, $note);
  }
}
