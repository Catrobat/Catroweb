<?php

declare(strict_types=1);

namespace App\Admin\System\CronJobs;

use App\DB\Entity\System\BackgroundJob;
use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use App\System\BackgroundJob\QueueStats;
use App\System\BackgroundJob\TrackedJobMessage;
use App\System\Cron\CronTaskRunner;
use App\System\Cron\CronTasks;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
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
    private readonly MessageBusInterface $message_bus,
    #[Autowire(service: 'messenger.transport.failed')]
    private readonly TransportInterface $failed_transport,
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

  /** Queues the job again on its own queue; a worker runs it, never this request. */
  public function retryFailedJobAction(Request $request): RedirectResponse
  {
    return $this->withFailedJob($request, function (Envelope $envelope): ?string {
      $message = $envelope->getMessage();
      if (!QueueStats::retryable($message)) {
        return null;
      }

      if ($message instanceof TrackedJobMessage) {
        // The failure ended its row: open it again, or the handler skips the finished job.
        $this->entity_manager->find(BackgroundJob::class, $message->jobId())?->requeue();
        $this->entity_manager->flush();
      }

      $this->message_bus->dispatch($message);

      return 'The job is queued again.';
    });
  }

  public function removeFailedJobAction(Request $request): RedirectResponse
  {
    return $this->withFailedJob($request, static fn (): string => 'The job was deleted.');
  }

  /** @param callable(Envelope): ?string $action runs before the job leaves the failed queue; null refuses */
  private function withFailedJob(Request $request, callable $action): RedirectResponse
  {
    $this->denyUnlessAllowed();
    $envelope = $this->failed_transport instanceof ListableReceiverInterface ? $this->failed_transport->find((string) $request->query->get('id')) : null;
    if (null === $envelope) {
      $this->addFlash('sonata_flash_error', 'No such job');

      return $this->toList();
    }

    $message = $action($envelope);
    if (null === $message) {
      $this->addFlash('sonata_flash_error', 'This job cannot be retried; delete it, the next scheduler tick runs the task.');

      return $this->toList();
    }

    $this->failed_transport->reject($envelope);
    $this->addFlash('sonata_flash_success', $message);

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
