<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationRequestValidator;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tokens are signed with a locally generated RSA key; the provider key sets are served by a Guzzle mock, so no network.
 *
 * @internal
 */
#[CoversClass(AuthenticationRequestValidator::class)]
class AuthenticationRequestValidatorTest extends TestCase
{
  private const string GOOGLE_ID = 'google-client-id';
  private const string APPLE_ID = 'org.catrobat.app';
  private const string KID = 'test-key';

  private static \OpenSSLAsymmetricKey $key;

  private ?string $fb_public_key_backup = null;

  #[\Override]
  public static function setUpBeforeClass(): void
  {
    $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    self::assertInstanceOf(\OpenSSLAsymmetricKey::class, $key);
    self::$key = $key;
  }

  #[\Override]
  protected function setUp(): void
  {
    $this->fb_public_key_backup = $_ENV['FB_OAUTH_PUBLIC_KEY'] ?? null;
    $details = openssl_pkey_get_details(self::$key);
    self::assertIsArray($details);
    $_ENV['FB_OAUTH_PUBLIC_KEY'] = $details['key'];
  }

  #[\Override]
  protected function tearDown(): void
  {
    if (null === $this->fb_public_key_backup) {
      unset($_ENV['FB_OAUTH_PUBLIC_KEY']);
    } else {
      $_ENV['FB_OAUTH_PUBLIC_KEY'] = $this->fb_public_key_backup;
    }
  }

  public function testUnknownProviderIsRejectedWithoutTouchingTheToken(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->validator()->verify('test', 'ee.ee.ee');
  }

  public function testValidGoogleTokenReturnsItsClaims(): void
  {
    $token = $this->sign(['iss' => 'https://accounts.google.com', 'aud' => self::GOOGLE_ID, 'sub' => 'g-1', 'email' => 'g@example.com', 'name' => 'G User', 'email_verified' => true] + $this->times());

    $this->assertSame(
      ['id' => 'g-1', 'email' => 'g@example.com', 'name' => 'G User', 'email_verified' => true],
      $this->validator()->verify('google', $token),
    );
  }

  public function testExpiredGoogleTokenIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://accounts.google.com', 'aud' => self::GOOGLE_ID, 'sub' => 'g-1', 'email' => 'g@example.com'] + $this->times(expired: true));

    $this->assertNull($this->validator()->verify('google', $token));
  }

  public function testGoogleTokenForAnotherClientIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://accounts.google.com', 'aud' => 'someone-else', 'sub' => 'g-1', 'email' => 'g@example.com'] + $this->times());

    $this->assertNull($this->validator()->verify('google', $token));
  }

  public function testValidFacebookTokenReturnsItsClaims(): void
  {
    $token = $this->sign(['user_id' => 'fb-1', 'email' => 'fb@example.com', 'name' => 'FB User', 'issued_at' => time() - 60, 'expires_at' => time() + 3600]);

    $this->assertSame(
      ['id' => 'fb-1', 'email' => 'fb@example.com', 'name' => 'FB User'],
      $this->validator()->verify('facebook', $token),
    );
  }

  public function testExpiredFacebookTokenIsInvalid(): void
  {
    $token = $this->sign(['user_id' => 'fb-1', 'email' => 'fb@example.com', 'name' => 'FB User', 'issued_at' => time() - 7200, 'expires_at' => time() - 3600]);

    $this->assertNull($this->validator()->verify('facebook', $token));
  }

  public function testFacebookTokenWithoutNameIsInvalid(): void
  {
    $token = $this->sign(['user_id' => 'fb-1', 'email' => 'fb@example.com', 'issued_at' => time() - 60, 'expires_at' => time() + 3600]);

    $this->assertNull($this->validator()->verify('facebook', $token));
  }

  public function testValidAppleTokenReturnsItsClaimsAndFetchesTheKeysOnce(): void
  {
    $token = $this->sign(['iss' => 'https://appleid.apple.com', 'aud' => self::APPLE_ID, 'sub' => '001234.abcd', 'email' => 'a@example.com', 'email_verified' => 'true'] + $this->times());

    // The mock holds one response only: a second key download would throw.
    $this->assertSame(
      ['id' => '001234.abcd', 'email' => 'a@example.com', 'email_verified' => 'true'],
      $this->validator()->verify('apple', $token),
    );
  }

  public function testExpiredAppleTokenIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://appleid.apple.com', 'aud' => self::APPLE_ID, 'sub' => '001234.abcd', 'email' => 'a@example.com'] + $this->times(expired: true));

    $this->assertNull($this->validator()->verify('apple', $token));
  }

  public function testAppleTokenForAnotherAppIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://appleid.apple.com', 'aud' => 'com.other.app', 'sub' => '001234.abcd', 'email' => 'a@example.com'] + $this->times());

    $this->assertNull($this->validator()->verify('apple', $token));
  }

  public function testAppleTokenFromAnotherIssuerIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://evil.example.com', 'aud' => self::APPLE_ID, 'sub' => '001234.abcd', 'email' => 'a@example.com'] + $this->times());

    $this->assertNull($this->validator()->verify('apple', $token));
  }

  public function testAppleTokenSignedWithAnUnknownKeyIsInvalid(): void
  {
    $token = $this->sign(['iss' => 'https://appleid.apple.com', 'aud' => self::APPLE_ID, 'sub' => '001234.abcd', 'email' => 'a@example.com'] + $this->times(), 'other-key');

    $this->assertNull($this->validator()->verify('apple', $token));
  }

  private function validator(): AuthenticationRequestValidator
  {
    $details = openssl_pkey_get_details(self::$key);
    self::assertIsArray($details);
    $jwks = json_encode(['keys' => [[
      'kty' => 'RSA',
      'kid' => self::KID,
      'alg' => 'RS256',
      'use' => 'sig',
      'n' => self::base64url($details['rsa']['n']),
      'e' => self::base64url($details['rsa']['e']),
    ]]], JSON_THROW_ON_ERROR);
    $http = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $jwks)]))]);

    return new AuthenticationRequestValidator(self::GOOGLE_ID, self::APPLE_ID, $http);
  }

  /**
   * @param array<string, mixed> $claims
   */
  private function sign(array $claims, string $kid = self::KID): string
  {
    return JWT::encode($claims, self::$key, 'RS256', $kid);
  }

  /**
   * @return array{iat: int, exp: int}
   */
  private function times(bool $expired = false): array
  {
    return $expired ? ['iat' => time() - 7200, 'exp' => time() - 3600] : ['iat' => time() - 60, 'exp' => time() + 3600];
  }

  private static function base64url(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
}
