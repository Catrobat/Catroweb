<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Enum\ContentType;
use App\Project\Event\ProjectAfterInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/** An upload or re-upload with an adults-only text is hidden for review right away; its images are scanned later. */
#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
class ProjectUploadModerationListener
{
  public function __construct(private readonly AutoModerationService $auto_moderation)
  {
  }

  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    $project = $event->getProjectEntity();
    $id = (string) $project->getId();
    $this->auto_moderation->flagAgeSignal(ContentType::Project, $id, $project->getName(), $project->getDescription(), $project->getCredits());
    $this->auto_moderation->queueImageScan(ContentType::Project, $id);
  }
}
