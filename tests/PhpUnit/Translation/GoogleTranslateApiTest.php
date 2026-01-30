<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\GoogleTranslateApi;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(GoogleTranslateApi::class)]
class GoogleTranslateApiTest extends TestCase
{
  private GoogleTranslateApi $api;

  private Stub|TranslateClient $client;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    // Use a stub by default; tests that verify behavior will create their own mock
    $this->client = $this->createStub(TranslateClient::class);
    $this->api = new GoogleTranslateApi($this->client, $this->createStub(LoggerInterface::class), 5);
  }

  /**
   * @throws Exception
   */
  public function testSuccessDetectedLanguage(): void
  {
    $client = $this->createMock(TranslateClient::class);
    $response = [
      'source' => 'en',
      'text' => 'translated',
    ];
    $client->expects($this->once())->method('translate')->willReturn($response);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5);

    $result = $api->translate('testing', null, 'fr');

    $this->assertEquals('en', $result->detected_source_language);
    $this->assertEquals('translated', $result->translation);
  }

  /**
   * @throws Exception
   */
  public function testSuccessSpecifiedLanguage(): void
  {
    $client = $this->createMock(TranslateClient::class);
    $response = [
      'source' => 'en',
      'text' => 'test',
    ];
    $client->expects($this->once())->method('translate')->willReturn($response);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5);

    $result = $api->translate('testing', 'en', 'fr');

    $this->assertNull($result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  /**
   * @throws Exception
   */
  public function testNullResponse(): void
  {
    $client = $this->createMock(TranslateClient::class);
    $client->expects($this->once())->method('translate')->willReturn(null);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5);

    $result = $api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   */
  public function testExceptionThrown(): void
  {
    $client = $this->createMock(TranslateClient::class);
    $exception = $this->createStub(ServiceException::class);
    $client->expects($this->once())->method('translate')->willThrowException($exception);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5);

    $result = $api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  public function testUnsupportedLanguage(): void
  {
    $this->assertEquals(0, $this->api->getPreference('testing', 'xx', 'en'));
    $this->assertEquals(0, $this->api->getPreference('testing', 'en', 'xx'));
  }

  public function testLongText(): void
  {
    $this->assertEquals(0.5, $this->api->getPreference('test12', null, 'en'));
    $this->assertEquals(0.5, $this->api->getPreference('Невтии', null, 'en'));
  }

  public function testShortText(): void
  {
    $this->assertEquals(1, $this->api->getPreference('test1', null, 'en'));
    $this->assertEquals(1, $this->api->getPreference('Невти', null, 'en'));
  }
}
