<?php

declare(strict_types=1);

namespace App\System\Cron;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RunDueCronTasksHandler
{
  public function __construct(private CronTaskRunner $runner)
  {
  }

  public function __invoke(RunDueCronTasks $message): void
  {
    $this->runner->runDue($message->slow);
  }
}
