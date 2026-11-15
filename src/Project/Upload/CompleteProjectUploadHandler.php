<?php

declare(strict_types=1);

namespace App\Project\Upload;

use App\Api\ProjectsApi;
use App\DB\Entity\System\BackgroundJob;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ProjectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

/** Completes a resumable upload on the uploads worker: the same code path as the synchronous completion call. */
#[AsMessageHandler]
final readonly class CompleteProjectUploadHandler
{
  public function __construct(private ProjectsApi $projects_api, private EntityManagerInterface $entity_manager)
  {
  }

  public function __invoke(CompleteProjectUpload $message): void
  {
    $job = $this->entity_manager->find(BackgroundJob::class, $message->job_id);
    if (null === $job || $job->isFinished()) {
      return;
    }

    $job->start();
    $this->entity_manager->flush();

    $user = $this->entity_manager->find(User::class, $message->user_id);
    $code = Response::HTTP_NOT_FOUND;
    $headers = [];
    $result = null === $user ? null : $this->projects_api->completeUpload($message->upload_id, $user, $message->accept_language, $message->ip, 'resumable-async', $code, $headers);

    if (Response::HTTP_CONFLICT === $code) {
      // A synchronous completion call holds the upload: its outcome is stored, the retry picks it up.
      throw new RecoverableMessageHandlingException('Upload '.$message->upload_id.' is being completed by another call');
    }

    $job = $this->entity_manager->find(BackgroundJob::class, $message->job_id) ?? $job;
    if (Response::HTTP_CREATED === $code && $result instanceof ProjectResponse) {
      $job->succeed((string) $result->getId());
    } else {
      $error = $result instanceof ErrorResponse ? $result->getError() : null;
      $job->fail($code >= 400 ? $code : Response::HTTP_INTERNAL_SERVER_ERROR, (string) ($error?->getMessage() ?? 'The upload could not be completed.'));
    }

    $this->entity_manager->flush();
  }
}
