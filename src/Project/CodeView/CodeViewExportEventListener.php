<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\Event\ProjectAfterInsertEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Exports an uploaded project's code view once the upload has been answered (kernel.terminate), so its first viewer
 * does not wait and the upload never does. If it fails, the first view tries again.
 */
#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
#[AsEventListener(event: KernelEvents::TERMINATE, method: 'onTerminate')]
class CodeViewExportEventListener
{
  /** @var list<Project> */
  private array $projects = [];

  public function __construct(
    private readonly CodeViewExportStore $store,
    private readonly LoggerInterface $logger,
  ) {
  }

  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    $this->projects[] = $event->getProjectEntity();
  }

  public function onTerminate(): void
  {
    $projects = $this->projects;
    $this->projects = [];
    foreach ($projects as $project) {
      try {
        $this->store->get($project);
      } catch (\Throwable $e) {
        $this->logger->error('Code view export after upload failed: '.$e->getMessage());
      }
    }
  }
}
