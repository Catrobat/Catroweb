<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\Entity\System\BackgroundJob;
use App\Project\Scratch\Converter\ScratchConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** Runs a queued Scratch import on the scratch worker, one at a time, and records the outcome for the polling client. */
#[AsMessageHandler]
final readonly class ConvertScratchProjectHandler
{
  public function __construct(private ScratchManager $scratch_manager, private EntityManagerInterface $entity_manager)
  {
  }

  public function __invoke(ConvertScratchProject $message): void
  {
    $job = $this->entity_manager->find(BackgroundJob::class, $message->job_id);
    if (null === $job || $job->isFinished()) {
      return;
    }

    $job->start();
    $this->entity_manager->flush();

    try {
      $project = $this->scratch_manager->importProject($message->scratch_id, static function (): void {}, $message->ip);
    } catch (ScratchConversionException $exception) {
      // Scratch being down, or a synchronous import holding the converter, passes: Messenger retries (a few times,
      // then BackgroundJobFailureListener fails the job). Not RecoverableMessageHandlingException: it retries forever.
      if (\in_array($exception->getCode(), [ScratchConversionException::UPSTREAM, ScratchConversionException::BUSY], true)) {
        throw new \RuntimeException($exception->getMessage(), 0, $exception);
      }

      // importProject cleared the EntityManager: load the job again.
      $job = $this->entity_manager->find(BackgroundJob::class, $message->job_id) ?? $job;
      $job->fail($exception->getCode() >= 400 ? $exception->getCode() : Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
      $this->entity_manager->flush();

      return;
    }

    $job = $this->entity_manager->find(BackgroundJob::class, $message->job_id) ?? $job;
    $job->succeed((string) $project->getId());
    $this->entity_manager->flush();
  }
}
