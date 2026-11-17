<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\LibreTranslateApi;
use App\Translation\UnsupportedLanguageException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

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

  public function testUnsupportedLanguageThrowsAndIsNotLoggedAsAFailure(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->never())->method('error');
    $logger->expects($this->once())->method('notice')->with($this->stringContains('Machine translation rejected'));
    $client = new MockHttpClient(new JsonMockResponse(['error' => 'xx is not supported'], ['http_code' => 400]));

    $this->expectException(UnsupportedLanguageException::class);

    $this->api($client, $logger)->translate('Hello', 'en', 'de');
  }

  public function testOtherClientAndServerErrorsAreLoggedAsAFailure(): void
  {
    foreach ([400, 403, 404, 429, 500] as $status) {
      $logger = $this->createMock(LoggerInterface::class);
      $logger->expects($this->once())->method('error')->with($this->stringContains('Machine translation failed'));
      $client = new MockHttpClient(new JsonMockResponse(['error' => 'busy'], ['http_code' => $status]));

      $this->assertNull($this->api($client, $logger)->translate('Hello', 'en', 'de'));
    }
  }

  public function testSiteWideLimitCountsLibreTranslateCalls(): void
  {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('error')->with($this->stringContains('site-wide translation limit reached'));
    $client = new MockHttpClient([new JsonMockResponse(['translatedText' => 'Hallo'])]);
    $limiter = new RateLimiterFactory(['id' => 'phpunit_lt_limited', 'policy' => 'fixed_window', 'limit' => 1, 'interval' => '1 minute'], new InMemoryStorage());
    $api = $this->api($client, $logger, $limiter);

    $this->assertSame(' ', $api->translate(' ', null, 'de')?->translation);
    $this->assertSame('Hallo', $api->translate('Hello', 'en', 'de')?->translation);
    $this->assertNull($api->translate('Hello', 'en', 'de'));
    $this->assertSame(1, $client->getRequestsCount());
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
    $logger->expects($this->once())->method('error')->with($this->stringContains('Machine translation failed'));
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
    $logger->expects($this->once())->method('error')->with($this->stringContains('LibreTranslate languages unavailable'));
    $client = new MockHttpClient(new JsonMockResponse(['error' => 'not a list']));

    $this->assertSame([], $this->api($client, $logger)->getSupportedLanguages());
  }

  private function api(MockHttpClient $client, ?LoggerInterface $logger = null, ?RateLimiterFactory $limiter = null): LibreTranslateApi
  {
    return new LibreTranslateApi($client, new ArrayAdapter(), $logger ?? new NullLogger(), 'http://lt:5000', $limiter ?? new RateLimiterFactory(['id' => 'phpunit_lt', 'policy' => 'no_limit'], new InMemoryStorage()));
  }
}
