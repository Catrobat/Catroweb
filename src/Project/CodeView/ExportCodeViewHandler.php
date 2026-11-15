<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\Project\ProjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ExportCodeViewHandler
{
  public function __construct(private ProjectManager $project_manager, private CodeViewExportStore $store, private LoggerInterface $logger)
  {
  }

  public function __invoke(ExportCodeView $message): void
  {
    $project = $this->project_manager->find($message->project_id);
    if (null === $project) {
      return; // deleted meanwhile
    }

    try {
      // A worker may wait for a free export slot; nothing is dropped.
      $this->store->refresh($project, true);
    } catch (CodeExportUnavailableException $e) {
      // Node failed on this project: retrying won't help, the first view tries again.
      $this->logger->info('Code view of project '.$message->project_id.' not exported: '.$e->getMessage());
    }
  }
}
