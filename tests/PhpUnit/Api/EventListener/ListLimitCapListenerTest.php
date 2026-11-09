<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\EventListener;

use App\Api\EventListener\ListLimitCapListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(ListLimitCapListener::class)]
final class ListLimitCapListenerTest extends TestCase
{
  #[DataProvider('provideRequests')]
  public function testCapsTheLimitOfApiRequests(string $uri, ?string $expected): void
  {
    $request = Request::create($uri);

    (new ListLimitCapListener())(new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST));

    $this->assertSame($expected, $request->query->get('limit'));
  }

  /**
   * @return iterable<string, array{string, ?string}>
   */
  public static function provideRequests(): iterable
  {
    yield 'huge API limit is capped' => ['/api/projects?limit=200000', '1000'];
    yield 'a limit beyond PHP_INT_MAX is capped' => ['/api/projects?limit=99999999999999999999999', '1000'];
    yield 'exponent notation is left to the API validation' => ['/api/projects?limit=1e9', '1e9'];
    yield 'decimals are left to the API validation' => ['/api/projects?limit=1001.5', '1001.5'];
    yield 'the app asking for 999 projects keeps 999' => ['/api/projects/user?limit=999', '999'];
    yield 'the cap itself is kept' => ['/api/projects?limit=1000', '1000'];
    yield 'non-numeric limits are left to the API validation' => ['/api/projects?limit=abc', 'abc'];
    yield 'no limit stays absent' => ['/api/projects', null];
    yield 'non-API routes are untouched' => ['/app/search?limit=200000', '200000'];
  }

  #[DataProvider('provideArrayLimitPaths')]
  public function testAnArrayLimitIsLeftAloneWithoutThrowing(string $uri): void
  {
    $request = Request::create($uri);

    (new ListLimitCapListener())(new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST));

    $this->assertSame(['1'], $request->query->all()['limit']);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function provideArrayLimitPaths(): iterable
  {
    yield 'API route' => ['/api/projects?limit[]=1'];
    yield 'web page' => ['/app/search?limit[]=1'];
  }
}
