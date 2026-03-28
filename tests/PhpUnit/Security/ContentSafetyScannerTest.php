<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\Security\ContentSafety\ContentSafetyScanner;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 *
 * @coversNothing
 */
class ContentSafetyScannerTest extends TestCase
{
  public function testScanReturnsSafeForCleanImage(): void
  {
    $mockResponse = new MockResponse(json_encode([
      'safe' => true,
      'nsfw_score' => 0.05,
      'safe_score' => 0.95,
      'label' => 'safe',
    ]));

    $scanner = new ContentSafetyScanner(
      new MockHttpClient($mockResponse),
      new NullLogger(),
      'http://localhost:5000',
      true,
      0.7,
    );

    $result = $scanner->scanImageBlob('fake-image-data');

    $this->assertTrue($result->safe);
    $this->assertSame('safe', $result->label);
    $this->assertLessThan(0.7, $result->nsfwScore);
  }

  public function testScanReturnsUnsafeForNsfwImage(): void
  {
    $mockResponse = new MockResponse(json_encode([
      'safe' => false,
      'nsfw_score' => 0.95,
      'safe_score' => 0.05,
      'label' => 'nsfw',
    ]));

    $scanner = new ContentSafetyScanner(
      new MockHttpClient($mockResponse),
      new NullLogger(),
      'http://localhost:5000',
      true,
      0.7,
    );

    $result = $scanner->scanImageBlob('fake-nsfw-data');

    $this->assertFalse($result->safe);
    $this->assertSame('nsfw', $result->label);
  }

  public function testScanSkippedWhenDisabled(): void
  {
    $scanner = new ContentSafetyScanner(
      new MockHttpClient(),
      new NullLogger(),
      'http://localhost:5000',
      false,
      0.7,
    );

    $result = $scanner->scanImageBlob('any-data');

    $this->assertTrue($result->safe);
    $this->assertTrue($result->skipped);
  }

  public function testScanFailsOpenOnNetworkError(): void
  {
    $mockResponse = new MockResponse('', ['http_code' => 500]);

    $scanner = new ContentSafetyScanner(
      new MockHttpClient($mockResponse),
      new NullLogger(),
      'http://localhost:5000',
      true,
      0.7,
    );

    $result = $scanner->scanImageBlob('fake-data');

    $this->assertTrue($result->safe);
    $this->assertTrue($result->unavailable);
  }

  public function testScanDataUri(): void
  {
    $mockResponse = new MockResponse(json_encode([
      'safe' => true,
      'nsfw_score' => 0.1,
      'safe_score' => 0.9,
      'label' => 'safe',
    ]));

    $scanner = new ContentSafetyScanner(
      new MockHttpClient($mockResponse),
      new NullLogger(),
      'http://localhost:5000',
      true,
      0.7,
    );

    $base64 = base64_encode('fake-image-data');
    $result = $scanner->scanDataUri("data:image/jpeg;base64,{$base64}");

    $this->assertTrue($result->safe);
  }
}
