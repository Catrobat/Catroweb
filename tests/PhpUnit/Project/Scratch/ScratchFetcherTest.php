<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
#[CoversClass(ScratchFetcher::class)]
final class ScratchFetcherTest extends TestCase
{
  public function testTooManyAssetsAreRejectedBeforeAnyDownload(): void
  {
    $requests = 0;
    $client = new MockHttpClient(static function () use (&$requests): MockResponse {
      ++$requests;

      return new MockResponse('');
    });
    $costumes = array_fill(0, ScratchFetcher::MAX_ASSETS + 1, ['md5ext' => md5('').'.svg']);

    try {
      (new ScratchFetcher($client))->fetchAssets(['targets' => [['costumes' => $costumes]]]);
      $this->fail('Too many assets were accepted');
    } catch (ScratchConversionException) {
    }

    $this->assertSame(0, $requests);
  }

  public function testAssetsAreDownloadedAndVerified(): void
  {
    $client = new MockHttpClient(static fn (): MockResponse => new MockResponse('<svg/>'));
    $md5ext = md5('<svg/>').'.svg';

    $this->assertSame([$md5ext => '<svg/>'], (new ScratchFetcher($client))->fetchAssets(['targets' => [['costumes' => [['md5ext' => $md5ext]], 'sounds' => []]]]));
  }

  #[DataProvider('failingScratch')]
  public function testFailuresSayWhetherScratchOrTheProjectIsToBlame(MockResponse $response, int $code): void
  {
    try {
      (new ScratchFetcher(new MockHttpClient($response)))->fetchMetadata(1);
      $this->fail('The failure was not reported');
    } catch (ScratchConversionException $exception) {
      $this->assertSame($code, $exception->getCode());
    }
  }

  /** @return iterable<string, array{MockResponse, int}> */
  public static function failingScratch(): iterable
  {
    yield 'unknown project' => [new MockResponse('', ['http_code' => 404]), ScratchConversionException::NOT_FOUND];
    yield 'Scratch error' => [new MockResponse('', ['http_code' => 503]), ScratchConversionException::UPSTREAM];
    yield 'unreachable' => [new MockResponse('', ['error' => 'Could not resolve host']), ScratchConversionException::UPSTREAM];
  }

  #[DataProvider('olderScratchProjects')]
  public function testProjectsFromOlderScratchVersionsAreExplained(string $body): void
  {
    try {
      (new ScratchFetcher(new MockHttpClient(new MockResponse($body))))->fetchProject(1, ['project_token' => 't']);
      $this->fail('An old project was accepted');
    } catch (ScratchConversionException $exception) {
      $this->assertSame(ScratchFetcher::OLD_SCRATCH_MESSAGE, $exception->getMessage());
    }
  }

  /** @return iterable<string, array{string}> */
  public static function olderScratchProjects(): iterable
  {
    yield 'Scratch 1.x' => ["ScratchV02\x00\x00\x1a"];
    yield 'Scratch 2 zip' => ["PK\x03\x04"];
    yield 'Scratch 2 JSON' => ['{"objName": "Stage", "children": []}'];
  }

  public function testAnOversizedDownloadIsTheProjectsFaultNotScratchs(): void
  {
    $client = new MockHttpClient(new MockResponse(str_repeat('x', 2 * 1024 * 1024)));

    try {
      (new ScratchFetcher($client))->fetchMetadata(1); // capped at 1 MiB
      $this->fail('The oversized download was accepted');
    } catch (ScratchConversionException $exception) {
      $this->assertSame(0, $exception->getCode());
    }
  }
}
