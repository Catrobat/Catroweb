<?php

declare(strict_types=1);

namespace App\System\Cron;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * Each lane is its own schedule, consumed by its own worker (messenger:consume scheduler_maintenance, and
 * scheduler_maintenance_slow), so an hour-long sweep never delays the popularity update. The lock keeps a second
 * worker of the same lane idle.
 */
abstract class CronLaneSchedule implements ScheduleProviderInterface
{
  public const string TICK = '5 minutes';

  public function __construct(private readonly LockFactory $locks)
  {
  }

  abstract protected function slow(): bool;

  #[\Override]
  public function getSchedule(): Schedule
  {
    return (new Schedule())
      ->add(RecurringMessage::every(self::TICK, new RunDueCronTasks($this->slow())))
      ->lock($this->locks->createLock(static::class))
    ;
  }
}
