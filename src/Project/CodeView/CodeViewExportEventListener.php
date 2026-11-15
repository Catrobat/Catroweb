<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\Project\Event\ProjectAfterInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Exports an uploaded project's code view on the default worker, so neither the upload nor its first viewer waits.
 * If it fails, the first view tries again.
 */
#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
class CodeViewExportEventListener
{
  public function __construct(
    private readonly CodeViewExportStore $store,
  ) {
  }

  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    $this->store->queue($event->getProjectEntity());
  }
}
