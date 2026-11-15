<?php

declare(strict_types=1);

namespace App\System\Cron;

use Symfony\Component\Scheduler\Attribute\AsSchedule;

#[AsSchedule('maintenance')]
final class MaintenanceSchedule extends CronLaneSchedule
{
  #[\Override]
  protected function slow(): bool
  {
    return false;
  }
}
