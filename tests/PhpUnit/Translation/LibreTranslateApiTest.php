<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\LibreTranslateApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

/**
 * @internal
 */
#[CoversClass(LibreTranslateApi::class)]
final class LibreTranslateApiTest extends TestCase
{
  public function testTranslatesWithTheGivenSourceLanguage(): void
  {
    $requests = [];
    $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$requests): JsonMockResponse {
      $requests[] = [$method, $url, json_decode((string) $options['body'], true)];

      return new JsonMockResponse(['translatedText' => 'Hallo']);
    });

    $result = $this->api($client)->translate('Hello', 'no-NO', 'zh-TW');

    $this->assertNotNull($result);
    $this->assertSame('Hallo', $result->translation);
    $this->assertNull($result->detected_source_language);
    $this->assertSame([['POST', 'http://lt:5000/translate', ['q' => 'Hello', 'source' => 'nb', 'target' => 'zh-Hant', 'format' => 'text']]], $requests);
  }

  public function testAutoDetectsTheSourceLanguage(): void
  {
    $body = null;
    $client = new MockHttpClient(static function (string $method, string $url, array $options) use (&$body): JsonMockResponse {
      $body = json_decode((string) $options['body'], true);

      return new JsonMockResponse(['translatedText' => 'Hello', 'detectedLanguage' => ['language' => 'nb', 'confidence' => 90.0]]);
    });

    $result = $this->api($client)->translate('Hei', null, 'en-GB');

    $this->assertSame('auto', $body['source']);
    $this->assertSame('en', $body['target']);
    $this->assertSame('no', $result?->detected_source_language);
  }

  public function testRejectedRequestIsNotLoggedAsAFailure(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('warning');
    $logger->expects($this->once())->method('notice')->with($this->stringContains('Machine translation rejected'));
    $client = new MockHttpClient(new JsonMockResponse(['error' => 'xx is not supported'], ['http_code' => 400]));

    $this->assertNull($this->api($client, $logger)->translate('Hello', 'en', 'de'));
  }

  public function testRateLimitedOrServerErrorIsLoggedAsAFailure(): void
  {
    foreach ([429, 500] as $status) {
      $logger = $this->createMock(LoggerInterface::class);
      $logger->expects($this->once())->method('warning')->with($this->stringContains('Machine translation failed'));
      $client = new MockHttpClient(new JsonMockResponse(['error' => 'busy'], ['http_code' => $status]));

      $this->assertNull($this->api($client, $logger)->translate('Hello', 'en', 'de'));
    }
  }

  public function testBlankTextIsNotSent(): void
  {
    $client = new MockHttpClient(static fn (): never => throw new \LogicException('no request expected'));

    $this->assertSame(' ', $this->api($client)->translate(' ', null, 'de')?->translation);
    $this->assertSame(0, $client->getRequestsCount());
  }

  public function testNetworkErrorIsLoggedAndReturnsNull(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('warning')->with($this->stringContains('Machine translation failed'));
    $client = new MockHttpClient(static fn (): never => throw new TransportException('Connection refused'));

    $this->assertNull($this->api($client, $logger)->translate('Hello', null, 'de'));
  }

  public function testSupportedLanguagesAreMappedAndCached(): void
  {
    $client = new MockHttpClient([new JsonMockResponse([['code' => 'en', 'name' => 'English'], ['code' => 'pt-BR', 'name' => 'Portuguese (Brazil)'], ['code' => 'zh-Hans', 'name' => 'Chinese']])]);
    $api = $this->api($client);

    $this->assertSame(['en', 'pt-BR', 'zh-CN'], $api->getSupportedLanguages());
    $this->assertSame(['en', 'pt-BR', 'zh-CN'], $api->getSupportedLanguages());
    $this->assertSame(1, $client->getRequestsCount());
  }

  public function testSupportedLanguagesAreEmptyWhileUnreachable(): void
  {
    $client = new MockHttpClient(static fn (): never => throw new TransportException('Connection refused'));

    $this->assertSame([], $this->api($client)->getSupportedLanguages());
  }

  public function testMalformedLanguageListIsTreatedAsUnavailable(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('warning')->with($this->stringContains('LibreTranslate languages unavailable'));
    $client = new MockHttpClient(new JsonMockResponse(['error' => 'not a list']));

    $this->assertSame([], $this->api($client, $logger)->getSupportedLanguages());
  }

  private function api(MockHttpClient $client, ?LoggerInterface $logger = null): LibreTranslateApi
  {
    return new LibreTranslateApi($client, new ArrayAdapter(), $logger ?? new NullLogger(), 'http://lt:5000');
  }
}
