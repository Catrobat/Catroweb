<?php

declare(strict_types=1);

namespace App\Admin\System\CronJobs;

use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use App\System\BackgroundJob\QueueStats;
use App\System\Cron\CronTaskRunner;
use App\System\Cron\CronTasks;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Background tasks: the scheduled tasks (cronjob table), the queues and the jobs that failed for good.
 *
 * @phpstan-extends CRUDController<CronJob>
 */
class CronJobsAdminController extends CRUDController
{
  public function __construct(
    protected CronJobRepository $cron_job_repository,
    protected EntityManagerInterface $entity_manager,
    private readonly CronTaskRunner $runner,
    private readonly QueueStats $queue_stats,
    #[Autowire('%kernel.project_dir%')]
    private readonly string $project_dir,
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    $tasks = [];
    foreach (CronTasks::all() as $task) {
      $job = $this->cron_job_repository->findByName($task->name);
      $tasks[] = ['task' => $task, 'job' => $job, 'next_run_at' => 'run' === $job?->getState() ? null : $this->runner->nextRunAt($task)];
    }

    $known = array_map(static fn (array $row): string => $row['task']->name, $tasks);
    $stale = array_filter($this->cron_job_repository->findAll(), static fn (CronJob $job): bool => !\in_array($job->getName(), $known, true));

    return $this->render('Admin/SystemManagement/DbUpdater/CronJobs.html.twig', [
      'action' => 'list',
      'tasks' => $tasks,
      'stale' => $stale,
      'queues' => $this->queue_stats->pending(),
      'failed' => $this->queue_stats->failed(),
    ]);
  }

  /** Forgets the task's last run, so the next scheduler tick (at most CronLaneSchedule::TICK away) runs it. */
  public function resetCronJobAction(Request $request): RedirectResponse
  {
    $this->denyUnlessAllowed();
    $job = $this->cron_job_repository->findByName((string) $request->query->get('id'));
    if (null === $job || 'run' === $job->getState()) {
      $this->addFlash('sonata_flash_error', null === $job ? 'No such task' : 'The task is running right now');

      return $this->toList();
    }

    $this->entity_manager->remove($job);
    $this->entity_manager->flush();
    $this->addFlash('sonata_flash_success', null === CronTasks::find($job->getName())
      ? 'Removed the record of a task that no longer exists.'
      : 'The task runs at the next scheduler tick (within a few minutes).');

    return $this->toList();
  }

  public function retryFailedJobAction(Request $request): RedirectResponse
  {
    return $this->failedJobCommand('messenger:failed:retry', $request, 'The job is queued again.');
  }

  public function removeFailedJobAction(Request $request): RedirectResponse
  {
    return $this->failedJobCommand('messenger:failed:remove', $request, 'The job was deleted.');
  }

  private function failedJobCommand(string $command, Request $request, string $success): RedirectResponse
  {
    $this->denyUnlessAllowed();
    $id = (string) $request->query->get('id');
    if (1 !== preg_match('/^\d+$/', $id)) {
      $this->addFlash('sonata_flash_error', 'No such job');

      return $this->toList();
    }

    // The Messenger commands know how to move a message between transports; a retry is only queued, never run here.
    $process = new Process(['bin/console', $command, $id, '--force', '--no-interaction'], $this->project_dir);
    $process->setTimeout(60);
    $process->run();
    $this->addFlash($process->isSuccessful() ? 'sonata_flash_success' : 'sonata_flash_error', $process->isSuccessful() ? $success : 'Failed: '.$process->getErrorOutput());

    return $this->toList();
  }

  private function denyUnlessAllowed(): void
  {
    if (!$this->admin->isGranted('RESET_CRON_JOB')) {
      throw new AccessDeniedException();
    }
  }

  private function toList(): RedirectResponse
  {
    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
