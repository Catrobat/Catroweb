<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Exceptions\ApiErrorResponse;
use App\Api\Services\AuthenticationManager;
use App\DB\Entity\System\BackgroundJob;
use App\DB\EntityRepository\System\BackgroundJobRepository;
use OpenAPI\Server\Api\JobsApiInterface;
use OpenAPI\Server\Model\BackgroundJobResponse;
use Symfony\Component\HttpFoundation\Response;

class JobsApi implements JobsApiInterface
{
  public function __construct(
    private readonly BackgroundJobRepository $job_repository,
    private readonly AuthenticationManager $authentication_manager,
  ) {
  }

  #[\Override]
  public function jobsJobIdGet(string $job_id, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $job = $this->job_repository->find($job_id);
    // A user's job (an upload) is theirs alone; others get the same 404 as for an unknown id.
    if (null === $job || (null !== $job->getUserId() && $job->getUserId() !== $this->authentication_manager->getAuthenticatedUser()?->getId())) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return ApiErrorResponse::createModel($responseCode, 'not_found', 'Job not found.');
    }

    $responseCode = Response::HTTP_OK;
    $responseHeaders['Cache-Control'] = 'no-store';

    return self::response($job);
  }

  public static function response(BackgroundJob $job): BackgroundJobResponse
  {
    return new BackgroundJobResponse([
      'id' => $job->getId(),
      'type' => $job->getType(),
      'state' => $job->getState(),
      'project_id' => $job->getResult(),
      'error_status' => $job->getErrorStatus(),
      'error_message' => $job->getErrorMessage(),
    ]);
  }
}
