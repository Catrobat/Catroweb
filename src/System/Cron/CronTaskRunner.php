<?php

declare(strict_types=1);

namespace App\System\Cron;

use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/** Runs due periodic tasks and records each run in the cronjob table, which the admin page shows. */
class CronTaskRunner
{
  private const int HOUR = 3600;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly CronJobRepository $cron_job_repository,
    private readonly LoggerInterface $logger,
    #[Autowire('%kernel.project_dir%')]
    private readonly string $project_dir,
    #[Autowire('%kernel.environment%')]
    private readonly string $environment,
  ) {
  }

  /** Runs every due task of one lane, one after another. */
  public function runDue(bool $slow): void
  {
    $this->recoverStuckJobs();
    foreach (CronTasks::all() as $task) {
      if ($task->slow === $slow && $this->isDue($task)) {
        $this->run($task);
      }
    }
  }

  public function isDue(CronTask $task): bool
  {
    $job = $this->cron_job_repository->findByName($task->name);
    if (null === $job || null === $job->getStartAt() || null === $job->getEndAt()) {
      return null === $job || 'run' !== $job->getState();
    }

    if ('run' === $job->getState()) {
      return false;
    }

    // A failed run is retried at the next tick, a successful one waits for its interval.
    return 0 !== $job->getResultCode() || (clone $job->getStartAt())->modify('+'.$task->interval) <= new \DateTime();
  }

  public function nextRunAt(CronTask $task): ?\DateTime
  {
    $job = $this->cron_job_repository->findByName($task->name);
    if (null === $job || null === $job->getStartAt() || 0 !== $job->getResultCode()) {
      return null; // due at the next tick
    }

    return (clone $job->getStartAt())->modify('+'.$task->interval);
  }

  public function run(CronTask $task): void
  {
    $job = $this->cron_job_repository->findByName($task->name) ?? (new CronJob())->setName($task->name);
    $job->setCroninterval($task->interval);
    $job->setState('run');
    $job->setStartAt(new \DateTime());
    $job->setTimeoutSeconds($task->timeout);
    $this->entity_manager->persist($job);
    $this->entity_manager->flush();

    $started = microtime(true);
    $process = new Process([...$task->command, '--env='.$this->environment], $this->project_dir);
    $process->setTimeout($task->timeout);
    try {
      $result_code = $process->run();
      $state = 'idle';
    } catch (ProcessTimedOutException) {
      $result_code = -1;
      $state = 'timeout';
    }

    if (0 !== $result_code) {
      $this->logger->error('Cron task failed', ['task' => $task->name, 'result_code' => $result_code, 'error' => mb_substr($process->getErrorOutput(), -2000)]);
    }

    $job->setResultCode($result_code);
    $job->setState($state);
    $job->setDurationSeconds((int) (microtime(true) - $started));
    $job->setEndAt(new \DateTime());
    $this->entity_manager->flush();

    $this->logger->info('Cron task finished', ['task' => $task->name, 'result_code' => $result_code, 'state' => $state, 'duration_seconds' => $job->getDurationSeconds()]);
  }

  /** A task left "running" by a crash or reboot runs again once it has been stuck for twice its timeout. */
  private function recoverStuckJobs(): void
  {
    foreach ($this->cron_job_repository->findBy(['state' => 'run']) as $job) {
      $start = $job->getStartAt();
      $max_seconds = 2 * max($job->getTimeoutSeconds() ?? self::HOUR, self::HOUR);
      if (null !== $start && time() - $start->getTimestamp() > $max_seconds) {
        $this->logger->warning('Recovering stuck cron task', ['task' => $job->getName()]);
        $job->setState('idle');
        $job->setResultCode(-2);
        $job->setEndAt(new \DateTime());
      }
    }

    $this->entity_manager->flush();
  }
}
