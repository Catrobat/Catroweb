<?php

declare(strict_types=1);

namespace App\Admin\Moderation;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Enum\ReportState;
use App\Moderation\ReportProcessor;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<ContentReport>
 */
class ModerationQueueController extends CRUDController
{
  public function __construct(
    private readonly ReportProcessor $report_processor,
  ) {
  }

  public function acceptReportAction(): RedirectResponse
  {
    return $this->resolveReport('accept');
  }

  public function rejectReportAction(): RedirectResponse
  {
    return $this->resolveReport('reject');
  }

  private function resolveReport(string $action): RedirectResponse
  {
    /** @var ContentReport|null $report */
    $report = $this->admin->getSubject();
    if (null === $report) {
      $this->addFlash('sonata_flash_error', 'Report not found');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    if (ReportState::New->value !== $report->getState()) {
      $this->addFlash('warning', 'Report #'.$report->getId().' is already resolved');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /** @var \App\DB\Entity\User\User $admin */
    $admin = $this->getUser();

    $this->report_processor->resolveReport($report, $admin, $action);

    $label = 'accept' === $action ? 'accepted - content hidden' : 'rejected - content restored';
    $this->addFlash('sonata_flash_success', 'Report #'.$report->getId().' '.$label);

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
