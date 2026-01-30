<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\ItranslateApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(ItranslateApi::class)]
class ItranslateApiTest extends TestCase
{
  private ItranslateApi $api;

  protected Stub $httpClient;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    // Use a stub by default; tests that verify behavior will create their own mock
    $this->httpClient = $this->createStub(Client::class);
    $this->api = new ItranslateApi($this->httpClient, 'fake', $this->createStub(LoggerInterface::class));
  }

  /**
   * @throws Exception
   * @throws \JsonException
   */
  public function testDetectLanguageCode(): void
  {
    $httpClient = $this->getMockBuilder(Client::class)->getMock();
    $httpClient
      ->expects($this->once())
      ->method('request')
      ->with('POST', '/translate/v1',
        $this->callback(function (array $subject): bool {
          $this->assertEquals('auto', $subject['json']['source']['dialect']);
          $this->assertEquals('en', $subject['json']['target']['dialect']);

          return true;
        }
        ))
      ->willReturn($this->mockGenericResponse())
    ;

    $api = new ItranslateApi($httpClient, 'fake', $this->createStub(LoggerInterface::class));
    $api->translate('testing', null, 'en');
  }

  /**
   * @throws Exception
   * @throws \JsonException
   */
  public function testSuccessDetectedLanguage(): void
  {
    $httpClient = $this->getMockBuilder(Client::class)->getMock();
    $response = $this->createMock(ResponseInterface::class);
    $response->expects($this->once())->method('getStatusCode')->willReturn(200);

    $json = '{
      "source": {
        "dialect": "auto",
        "text": "testing",
        "detected": "en"
      },
      "target": {
        "dialect": "fr",
        "text": "test"
      }
    }';

    $body = $this->createMock(StreamInterface::class);
    $body->expects($this->once())->method('getContents')->willReturn($json);
    $response->expects($this->once())->method('getBody')->willReturn($body);

    $httpClient->expects($this->once())->method('request')->willReturn($response);

    $api = new ItranslateApi($httpClient, 'fake', $this->createStub(LoggerInterface::class));
    $result = $api->translate('testing', null, 'fr');

    $this->assertEquals('en', $result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  /**
   * @throws Exception
   * @throws \JsonException
   */
  public function testSuccessSpecifiedLanguage(): void
  {
    $httpClient = $this->getMockBuilder(Client::class)->getMock();
    $response = $this->createMock(ResponseInterface::class);
    $response->expects($this->once())->method('getStatusCode')->willReturn(200);

    $json = '{
      "source": {
        "dialect": "en",
        "text": "testing"
      },
      "target": {
        "dialect": "fr",
        "text": "test"
      }
    }';

    $body = $this->createMock(StreamInterface::class);
    $body->expects($this->once())->method('getContents')->willReturn($json);
    $response->expects($this->once())->method('getBody')->willReturn($body);

    $httpClient->expects($this->once())->method('request')->willReturn($response);

    $api = new ItranslateApi($httpClient, 'fake', $this->createStub(LoggerInterface::class));
    $result = $api->translate('testing', 'en', 'fr');

    $this->assertNull($result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  /**
   * @throws Exception
   * @throws \JsonException
   */
  public function testNon200StatusCode(): void
  {
    $httpClient = $this->getMockBuilder(Client::class)->getMock();
    $response = $this->createMock(ResponseInterface::class);
    $response->expects($this->once())->method('getStatusCode')->willReturn(400);
    $httpClient->expects($this->once())->method('request')->willReturn($response);

    $api = new ItranslateApi($httpClient, 'fake', $this->createStub(LoggerInterface::class));
    $result = $api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  /**
   * @throws Exception
   * @throws \JsonException
   */
  public function testExceptionThrown(): void
  {
    $httpClient = $this->getMockBuilder(Client::class)->getMock();
    $exception = $this->createStub(GuzzleException::class);
    $httpClient->expects($this->once())->method('request')->willThrowException($exception);

    $api = new ItranslateApi($httpClient, 'fake', $this->createStub(LoggerInterface::class));
    $result = $api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  public function testUnsupportedLanguage(): void
  {
    $this->assertEquals(0, $this->api->getPreference('testing', 'xx', 'en'));
    $this->assertEquals(0, $this->api->getPreference('testing', 'en', 'xx'));
  }

  public function testSupportedLanguage(): void
  {
    $this->assertEquals(0.5, $this->api->getPreference('testing', null, 'en'));
    $this->assertEquals(0.5, $this->api->getPreference('testing', 'de', 'en'));
  }

  /**
   * @throws Exception
   */
  private function mockGenericResponse(): ResponseInterface
  {
    $response = $this->createMock(ResponseInterface::class);
    $response->expects($this->once())->method('getStatusCode')->willReturn(200);

    $json = '{
      "source": {
        "dialect": "auto",
        "text": "testing",
        "detected": "en"
      },
      "target": {
        "dialect": "fr",
        "text": "test"
      }
    }';

    $body = $this->createMock(StreamInterface::class);
    $body->expects($this->once())->method('getContents')->willReturn($json);
    $response->expects($this->once())->method('getBody')->willReturn($body);

    return $response;
  }
}
