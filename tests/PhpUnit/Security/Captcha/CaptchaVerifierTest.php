<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Captcha;

use App\Security\Captcha\CaptchaVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(CaptchaVerifier::class)]
final class CaptchaVerifierTest extends TestCase
{
  #[Group('unit')]
  public function testVerifyAutoPassesInTestEnv(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'test');

    $result = $verifier->verify('any-token');

    $this->assertTrue($result['success']);
    $this->assertSame('test-auto-pass', $result['result']);
  }

  #[Group('unit')]
  public function testVerifyForcedFailureInTestEnv(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'test');

    $result = $verifier->verify('fail');

    $this->assertFalse($result['success']);
    $this->assertSame('test-forced-failure', $result['result']);
  }

  #[Group('unit')]
  public function testVerifyReturnsMissingTokenForNull(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'prod');

    $result = $verifier->verify(null);

    $this->assertFalse($result['success']);
    $this->assertSame('missing-token', $result['result']);
  }

  #[Group('unit')]
  public function testVerifyReturnsMissingTokenForEmpty(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'prod');

    $result = $verifier->verify('');

    $this->assertFalse($result['success']);
    $this->assertSame('missing-token', $result['result']);
  }

  #[Group('unit')]
  public function testVerifyReturnsVerifiedOnSuccess(): void
  {
    $response = $this->createStub(ResponseInterface::class);
    $response->method('toArray')->willReturn(['success' => true]);

    $httpClient = $this->createStub(HttpClientInterface::class);
    $httpClient->method('request')->willReturn($response);

    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'prod');

    $result = $verifier->verify('valid-token');

    $this->assertTrue($result['success']);
    $this->assertSame('verified', $result['result']);
  }

  #[Group('unit')]
  public function testVerifyReturnsFailureOnRejection(): void
  {
    $response = $this->createStub(ResponseInterface::class);
    $response->method('toArray')->willReturn(['success' => false]);

    $httpClient = $this->createStub(HttpClientInterface::class);
    $httpClient->method('request')->willReturn($response);

    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'prod');

    $result = $verifier->verify('invalid-token');

    $this->assertFalse($result['success']);
    $this->assertSame('verification-failed', $result['result']);
  }

  #[Group('unit')]
  public function testVerifySendsJsonWithSecret(): void
  {
    $response = $this->createStub(ResponseInterface::class);
    $response->method('toArray')->willReturn(['success' => true]);

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with(
        'POST',
        'http://cap/site/siteverify',
        $this->callback(function (array $options): bool {
          return isset($options['json']['secret'])
            && 'my-secret' === $options['json']['secret']
            && 'token-123' === $options['json']['response'];
        })
      )
      ->willReturn($response)
    ;

    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'my-secret', true, 'prod');

    $result = $verifier->verify('token-123');

    $this->assertTrue($result['success']);
  }

  #[Group('unit')]
  public function testVerifyAutoPassesWhenDisabled(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', false, 'prod');

    $result = $verifier->verify(null);

    $this->assertTrue($result['success']);
    $this->assertSame('disabled', $result['result']);
  }

  #[Group('unit')]
  public function testIsEnabledReturnsFalseWhenDisabled(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', false, 'prod');

    $this->assertFalse($verifier->isEnabled());
  }

  #[Group('unit')]
  public function testIsEnabledReturnsFalseInTestEnv(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'test');

    $this->assertFalse($verifier->isEnabled());
  }

  #[Group('unit')]
  public function testIsEnabledReturnsTrueWhenEnabledInProd(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);
    $verifier = new CaptchaVerifier($httpClient, 'http://cap/site/siteverify', 'secret', true, 'prod');

    $this->assertTrue($verifier->isEnabled());
  }
}
