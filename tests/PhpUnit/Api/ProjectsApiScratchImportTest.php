<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Projects\ProjectsResponseManager;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\DB\Entity\Project\Project;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\Remix\RemixManager;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\ScratchManager;
use App\Storage\ScreenshotRepository;
use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ProjectResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * Status mapping of POST /api/projects/scratch/{id}; the Behat feature covers the real conversions.
 *
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
#[Group('unit')]
final class ProjectsApiScratchImportTest extends TestCase
{
  public function testConversionAnswers201WithLocation(): void
  {
    $api = $this->createApi(static function (int $id, callable $before_conversion): Project {
      $before_conversion();

      return new Project();
    });

    [$code, $headers, $result] = $this->import($api);

    $this->assertSame(201, $code);
    $this->assertSame('/app/project/converted', $headers['Location']);
    $this->assertInstanceOf(ProjectResponse::class, $result);
  }

  public function testUpToDateCopyAnswers200(): void
  {
    [$code, $headers, $result] = $this->import($this->createApi(static fn (): Project => new Project()));

    $this->assertSame(200, $code);
    $this->assertArrayNotHasKey('Location', $headers);
    $this->assertInstanceOf(ProjectResponse::class, $result);
  }

  #[DataProvider('failures')]
  public function testFailuresMapToStatusAndKeepTheMessage(int $exception_code, int $status, string $type): void
  {
    $api = $this->createApi(static function () use ($exception_code): never {
      throw new ScratchConversionException('Shown to the user', $exception_code);
    });

    [$code, $headers, $result] = $this->import($api);

    $this->assertSame($status, $code);
    $this->assertInstanceOf(ErrorResponse::class, $result);
    $error = $result->getError();
    $this->assertNotNull($error);
    $this->assertSame($type, $error->getType());
    $this->assertSame('Shown to the user', $error->getMessage());
    $this->assertSame(503 === $status ? '60' : null, $headers['Retry-After'] ?? null);
  }

  /**
   * @return array<string, array{int, int, string}>
   */
  public static function failures(): array
  {
    return [
      'not found' => [ScratchConversionException::NOT_FOUND, 404, 'not_found'],
      'rate limited' => [ScratchConversionException::RATE_LIMITED, 429, 'too_many_requests'],
      'busy' => [ScratchConversionException::BUSY, 503, 'service_unavailable'],
      'unconvertible' => [0, 422, 'unprocessable_entity'],
      'Scratch unreachable' => [ScratchConversionException::UPSTREAM, 502, 'bad_gateway'],
      'unexpected failure' => [ScratchConversionException::INTERNAL, 500, 'internal_server_error'],
    ];
  }

  /** @param array<string, string> $headers */
  #[DataProvider('origins')]
  public function testBrowsersMayOnlyImportFromThisSite(array $headers, int $status): void
  {
    $request = Request::create('http://share.catrobat.org/api/projects/scratch/70058680', 'POST');
    $request->headers->add($headers);
    $requests = new RequestStack();
    $requests->push($request);

    [$code, , $result] = $this->import($this->createApi(static fn (): Project => new Project(), requests: $requests));

    $this->assertSame($status, $code);
    if (403 === $status) {
      $this->assertInstanceOf(ErrorResponse::class, $result);
    }
  }

  /** @return array<string, array{array<string, string>, int}> */
  public static function origins(): array
  {
    return [
      'app or script, no Origin' => [[], 200],
      'this site' => [['Origin' => 'https://share.catrobat.org', 'Sec-Fetch-Site' => 'same-origin'], 200],
      'this site behind a TLS proxy' => [['Origin' => 'https://share.catrobat.org'], 200],
      'form on another site' => [['Origin' => 'https://evil.example'], 403],
      'another site, by fetch metadata' => [['Sec-Fetch-Site' => 'cross-site'], 403],
      'opaque origin' => [['Origin' => 'null'], 403],
    ];
  }

  public function testOpeningIsRateLimitedPerClient(): void
  {
    $api = $this->createApi(static fn (): Project => new Project(), open_limit: 1);

    $this->assertSame(200, $this->import($api)[0]);
    $this->assertSame(429, $this->import($api)[0]);
  }

  public function testConvertingIsRateLimitedPerClient(): void
  {
    $api = $this->createApi(static function (int $id, callable $before_conversion): Project {
      $before_conversion();

      return new Project();
    }, convert_limit: 1);

    $this->assertSame(201, $this->import($api)[0]);
    $this->assertSame(429, $this->import($api)[0]);
  }

  /**
   * @return array{int, array<string, string>, mixed}
   */
  private function import(ProjectsApi $api): array
  {
    $code = 0;
    $headers = [];
    $result = $api->projectsScratchScratchIdPost(70058680, $code, $headers);

    return [$code, $headers, $result];
  }

  private function createApi(\Closure $import, int $open_limit = 100, int $convert_limit = 100, RequestStack $requests = new RequestStack()): ProjectsApi
  {
    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('getClientIp')->willReturn('1.2.3.4');
    $response_manager = $this->createStub(ProjectsResponseManager::class);
    $response_manager->method('createProjectLocation')->willReturn('/app/project/converted');
    $response_manager->method('createProjectDataResponse')->willReturn(new ProjectResponse());
    $facade = $this->createStub(ProjectsApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);
    $facade->method('getResponseManager')->willReturn($response_manager);
    $scratch_manager = $this->createStub(ScratchManager::class);
    $scratch_manager->method('importProject')->willReturnCallback($import);
    $limiter = static fn (string $id, int $limit): RateLimiterFactory => new RateLimiterFactory(['id' => $id, 'policy' => 'sliding_window', 'limit' => $limit, 'interval' => '1 minute'], new InMemoryStorage());
    $unlimited = new RateLimiterFactory(['id' => 'unlimited', 'policy' => 'no_limit'], new InMemoryStorage());

    return new ProjectsApi(
      $facade,
      $this->createStub(ReactionsApiFacade::class),
      $this->createStub(CodeTreeBuilder::class),
      $this->createStub(RemixManager::class),
      $this->createStub(ScreenshotRepository::class),
      $unlimited,
      $unlimited,
      $unlimited,
      $requests,
      new NullLogger(),
      $limiter('open', $open_limit),
      $limiter('burst', $convert_limit),
      $limiter('daily', $convert_limit),
      $scratch_manager,
    );
  }
}
