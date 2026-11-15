<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\BackgroundJob;

use App\Api\JobsApi;
use App\Api\Services\AuthenticationManager;
use App\DB\Entity\System\BackgroundJob;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\System\BackgroundJobRepository;
use OpenAPI\Server\Model\BackgroundJobResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(JobsApi::class)]
final class JobsApiTest extends TestCase
{
  public function testAnAnonymousJobIsShownToAnyone(): void
  {
    $job = new BackgroundJob('scratch_import', '70058680');
    $job->succeed('project-1');

    [$code, $result] = $this->get($job, null);

    $this->assertSame(200, $code);
    $this->assertInstanceOf(BackgroundJobResponse::class, $result);
    $this->assertSame('done', $result->getState());
    $this->assertSame('project-1', $result->getProjectId());
  }

  public function testAUsersJobIsHiddenFromOthers(): void
  {
    $job = new BackgroundJob('project_upload', 'upload-1', 'owner');

    $this->assertSame(404, $this->get($job, 'someone-else')[0]);
    $this->assertSame(404, $this->get($job, null)[0]);
    $this->assertSame(200, $this->get($job, 'owner')[0]);
  }

  public function testAnUnknownJobIs404(): void
  {
    $this->assertSame(404, $this->get(null, null)[0]);
  }

  /** @return array{int, mixed} */
  private function get(?BackgroundJob $job, ?string $user_id): array
  {
    $repository = $this->createStub(BackgroundJobRepository::class);
    $repository->method('find')->willReturn($job);
    $authentication = $this->createStub(AuthenticationManager::class);
    $user = null;
    if (null !== $user_id) {
      $user = new User();
      $user->setId($user_id);
    }

    $authentication->method('getAuthenticatedUser')->willReturn($user);
    $code = 0;
    $headers = [];
    $result = new JobsApi($repository, $authentication)->jobsJobIdGet('id', $code, $headers);

    return [$code, $result];
  }
}
