<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\ItranslateApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @covers \App\Translation\ItranslateApi
 */
class ItranslateApiTest extends TestCase
{
  private ItranslateApi $api;

  protected MockObject $httpClient;

  #[\Override]
  protected function setUp(): void
  {
    $this->httpClient = $this->getMockBuilder(Client::class)->getMock();

    $this->api = new ItranslateApi($this->httpClient, $this->createMock(LoggerInterface::class));
  }

  public function testDetectLanguageCode(): void
  {
    $this->httpClient
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

    $this->api->translate('testing', null, 'en');
  }

  public function testSuccessDetectedLanguage(): void
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

    $this->httpClient->expects($this->once())->method('request')->willReturn($response);

    $result = $this->api->translate('testing', null, 'fr');

    $this->assertEquals('en', $result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  public function testSuccessSpecifiedLanguage(): void
  {
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

    $this->httpClient->expects($this->once())->method('request')->willReturn($response);

    $result = $this->api->translate('testing', 'en', 'fr');

    $this->assertNull($result->detected_source_language);
    $this->assertEquals('test', $result->translation);
  }

  public function testNon200StatusCode(): void
  {
    $response = $this->createMock(ResponseInterface::class);
    $response->expects($this->once())->method('getStatusCode')->willReturn(400);
    $this->httpClient->expects($this->once())->method('request')->willReturn($response);

    $result = $this->api->translate('testing', null, 'fr');

    $this->assertNull($result);
  }

  public function testExceptionThrown(): void
  {
    $exception = $this->createMock(GuzzleException::class);
    $this->httpClient->expects($this->once())->method('request')->willThrowException($exception);

    $result = $this->api->translate('testing', null, 'fr');

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
