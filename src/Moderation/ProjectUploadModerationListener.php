<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Enum\ContentType;
use App\Project\Event\ProjectAfterInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * An upload or re-upload with an adults-only text is hidden for review right away; its images are scanned later.
 * Restores from a backup are left alone: they bring back what was there.
 */
#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
class ProjectUploadModerationListener
{
  public function __construct(private readonly AutoModerationService $auto_moderation)
  {
  }

  /** The upload moves the extracted files into place after this listener; the worker waits that long. */
  public const int SCAN_DELAY_MS = 30_000;

  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    if ($event->isRestore()) {
      return;
    }

    $project = $event->getProjectEntity();
    $id = (string) $project->getId();
    $this->auto_moderation->flagAgeSignal(ContentType::Project, $id, $project->getName(), $project->getDescription(), $project->getCredits());
    $this->auto_moderation->queueImageScan(ContentType::Project, $id, self::SCAN_DELAY_MS);
  }
}
