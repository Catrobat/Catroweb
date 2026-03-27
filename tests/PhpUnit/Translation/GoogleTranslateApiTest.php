<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\GoogleTranslateApi;
use App\Translation\GoogleTranslateClientInterface;
use Google\ApiCore\ApiException;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use Google\Cloud\Translate\V3\Translation;
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

  private Stub|GoogleTranslateClientInterface $client;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->client = $this->createStub(GoogleTranslateClientInterface::class);
    $this->api = new GoogleTranslateApi($this->client, $this->createStub(LoggerInterface::class), 5, 'test-project');
  }

  /**
   * @throws Exception
   */
  public function testSuccessDetectedLanguage(): void
  {
    $client = $this->createMock(GoogleTranslateClientInterface::class);

    $translation = $this->createStub(Translation::class);
    $translation->method('getDetectedLanguageCode')->willReturn('en');
    $translation->method('getTranslatedText')->willReturn('translated');

    $response = $this->createStub(TranslateTextResponse::class);
    $response->method('getTranslations')->willReturn([$translation]);

    $client->expects($this->once())->method('translateText')->willReturn($response);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5, 'test-project');

    $result = $api->translate('testing', null, 'fr');

    $this->assertEquals('en', $result->detected_source_language);
    $this->assertEquals('translated', $result->translation);
  }

  /**
   * @throws Exception
   */
  public function testSuccessSpecifiedLanguage(): void
  {
    $client = $this->createMock(GoogleTranslateClientInterface::class);

    $translation = $this->createStub(Translation::class);
    $translation->method('getTranslatedText')->willReturn('test');

    $response = $this->createStub(TranslateTextResponse::class);
    $response->method('getTranslations')->willReturn([$translation]);

    $client->expects($this->once())->method('translateText')->willReturn($response);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5, 'test-project');

    $result = $api->translate('testing', 'en', 'fr');

    $this->assertNull($result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  /**
   * @throws Exception
   */
  public function testEmptyTranslationsResponse(): void
  {
    $client = $this->createMock(GoogleTranslateClientInterface::class);

    $response = $this->createStub(TranslateTextResponse::class);
    $response->method('getTranslations')->willReturn([]);

    $client->expects($this->once())->method('translateText')->willReturn($response);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5, 'test-project');

    $result = $api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   */
  public function testExceptionThrown(): void
  {
    $client = $this->createMock(GoogleTranslateClientInterface::class);
    $exception = new ApiException('test error', 500, 'INTERNAL');
    $client->expects($this->once())->method('translateText')->willThrowException($exception);
    $api = new GoogleTranslateApi($client, $this->createStub(LoggerInterface::class), 5, 'test-project');

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
