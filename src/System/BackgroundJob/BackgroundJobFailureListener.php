<?php

declare(strict_types=1);

namespace App\System\BackgroundJob;

use App\DB\Entity\System\BackgroundJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/**
 * Handlers record the failures they expect themselves. Anything else (a crash, or retries used up) still has to
 * end the job, or its client polls forever.
 */
#[AsEventListener]
final readonly class BackgroundJobFailureListener
{
  public function __construct(private EntityManagerInterface $entity_manager)
  {
  }

  public function __invoke(WorkerMessageFailedEvent $event): void
  {
    $message = $event->getEnvelope()->getMessage();
    if ($event->willRetry() || !$message instanceof TrackedJobMessage) {
      return;
    }

    if (!$this->entity_manager->isOpen()) {
      return; // a fresh worker process picks the next message; this row stays "running" and is cleaned up later
    }

    $this->entity_manager->clear();
    $job = $this->entity_manager->find(BackgroundJob::class, $message->jobId());
    if (null === $job || $job->isFinished()) {
      return;
    }

    $job->fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong. Please try again later.');
    $this->entity_manager->flush();
  }
}
