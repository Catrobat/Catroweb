<?php

declare(strict_types=1);

namespace App\System\Cron;

final readonly class CronTask
{
  /**
   * @param list<string> $command  console command, run in its own process so its memory is returned
   * @param string       $interval relative time ("1 day") between successful runs
   * @param bool         $slow     runs on the slow worker: may take long, so it never delays the fast tasks
   */
  public function __construct(
    public string $name,
    public array $command,
    public string $interval,
    public int $timeout,
    public bool $slow = false,
  ) {
  }
}
