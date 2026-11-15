<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Cron;

use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use App\System\Cron\CronTask;
use App\System\Cron\CronTaskRunner;
use App\System\Cron\CronTasks;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 */
#[CoversClass(CronTaskRunner::class)]
#[CoversClass(CronTasks::class)]
final class CronTaskRunnerTest extends TestCase
{
  #[DataProvider('runs')]
  public function testATaskIsDueWhenItsIntervalPassedSinceItsLastSuccessfulRun(?CronJob $job, bool $due): void
  {
    $this->assertSame($due, $this->runner($job)->isDue(new CronTask('Task', ['bin/console', 'list'], '1 day', 60)));
  }

  /** @return iterable<string, array{?CronJob, bool}> */
  public static function runs(): iterable
  {
    yield 'never ran' => [null, true];
    yield 'ran an hour ago' => [self::job('-1 hour', 0), false];
    yield 'ran two days ago' => [self::job('-2 days', 0), true];
    yield 'failed an hour ago' => [self::job('-1 hour', 1), true];
    yield 'running' => [self::job('-1 hour', null, 'run'), false];
  }

  public function testTaskNamesAreUnique(): void
  {
    $names = array_map(static fn (CronTask $task): string => $task->name, CronTasks::all());

    $this->assertSame(array_unique($names), $names, 'names are the cronjob table key');
  }

  private static function job(string $started, ?int $result, string $state = 'idle'): CronJob
  {
    $job = new CronJob()->setName('Task');
    $job->setStartAt(new \DateTime($started));
    $job->setEndAt('run' === $state ? null : new \DateTime($started));
    $job->setResultCode($result);
    $job->setState($state);

    return $job;
  }

  private function runner(?CronJob $job): CronTaskRunner
  {
    $repository = $this->createStub(CronJobRepository::class);
    $repository->method('findByName')->willReturn($job);

    return new CronTaskRunner($this->createStub(EntityManagerInterface::class), $repository, new NullLogger(), '/tmp', 'test');
  }
}
