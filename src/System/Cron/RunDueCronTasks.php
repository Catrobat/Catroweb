<?php

declare(strict_types=1);

namespace App\System\Cron;

/** Scheduler tick: run the due tasks of one lane. */
final readonly class RunDueCronTasks
{
  public function __construct(public bool $slow)
  {
  }
}
