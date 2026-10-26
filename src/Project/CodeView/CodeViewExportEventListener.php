<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\Project\Event\ProjectAfterInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Exports an uploaded project's code view once the upload has been answered (CodeViewExportStore::refreshQueued on
 * kernel.terminate), so its first viewer does not wait and the upload never does. If it fails, the first view tries
 * again.
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
