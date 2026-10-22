<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
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
}
