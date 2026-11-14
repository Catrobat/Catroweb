<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractRequestValidator;
use CoderCat\JWKToPEM\JWKConverter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AuthenticationRequestValidator extends AbstractRequestValidator
{
  public function __construct(
    #[Autowire('%env(string:GOOGLE_ID)%')]
    private readonly string $google_client_id,
    #[Autowire('%env(string:APPLE_ID)%')]
    private readonly string $apple_client_id,
    private readonly ClientInterface $http_client = new Client(),
  ) {
  }

  /**
   * Verifies the provider's ID token once and returns its normalized claims.
   *
   * @return array{id: mixed, email: mixed, name?: mixed, email_verified?: mixed}|null null when the token is invalid
   *
   * @throws \InvalidArgumentException when the provider is unknown
   * @throws GuzzleException
   * @throws \JsonException
   */
  public function verify(string $provider, string $id_token): ?array
  {
    return match ($provider) {
      'google' => $this->verifyGoogleIdToken($id_token),
      'facebook' => $this->verifyFacebookIdToken($id_token),
      'apple' => $this->verifyAppleIdToken($id_token),
      default => throw new \InvalidArgumentException(sprintf('Unknown OAuth provider "%s"', $provider)),
    };
  }

  /**
   * @return array{id: mixed, email: mixed, name: mixed, email_verified: mixed}|null
   */
  private function verifyGoogleIdToken(string $id_token): ?array
  {
    try {
      // Specify the CLIENT_ID of the app that accesses the backend
      $client = new \Google\Client(['client_id' => $this->google_client_id]);
      $client->setHttpClient($this->http_client);
      $payload = $client->verifyIdToken($id_token);
    } catch (\Exception) {
      return null;
    }

    if (false === $payload) {
      return null;
    }

    return [
      'id' => $payload['sub'] ?? null,
      'email' => $payload['email'] ?? null,
      'name' => $payload['name'] ?? null,
      'email_verified' => $payload['email_verified'] ?? false,
    ];
  }

  /**
   * @return array{id: mixed, email: mixed, name: mixed}|null
   */
  private function verifyFacebookIdToken(string $id_token): ?array
  {
    try {
      $public_key = $_ENV['FB_OAUTH_PUBLIC_KEY'] ?? '';
      $decoded = JWT::decode($id_token, new Key($public_key, 'RS256'));
    } catch (\Exception) {
      return null;
    }

    if ($decoded->expires_at < time() || $decoded->issued_at > time() || empty($decoded->user_id) || !isset($decoded->email) || !isset($decoded->name)) {
      return null;
    }

    return [
      'id' => $decoded->user_id,
      'email' => $decoded->email,
      'name' => $decoded->name,
    ];
  }

  /**
   * @return array{id: mixed, email: mixed, email_verified: mixed}|null
   *
   * @throws GuzzleException
   * @throws \JsonException
   */
  private function verifyAppleIdToken(string $id_token): ?array
  {
    $jwt = self::jwt_decode($id_token);
    if (null === $jwt || [] === $jwt || !isset($jwt['header']['kid'])) {
      return null;
    }

    $header = $jwt['header'];
    $res = $this->http_client->request('GET', 'https://appleid.apple.com/auth/keys');
    $body = $res->getBody()->getContents();
    $keys_raw = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    $keys = $keys_raw['keys'];
    $public_key = [];
    foreach ($keys as $key) {
      if ($header['kid'] === $key['kid']) {
        $public_key = $key;
        break;
      }
    }

    if ((is_countable($public_key) ? count($public_key) : 0) < 1) {
      return null;
    }

    $jwkConverter = new JWKConverter();

    try {
      $PEM = $jwkConverter->toPEM($public_key);
      $decoded = JWT::decode($id_token, new Key($PEM, 'RS256'));
    } catch (\Exception) {
      return null;
    }

    if ($decoded->exp < time() || $decoded->iat > time() || $decoded->aud !== $this->apple_client_id
      || 'https://appleid.apple.com' !== $decoded->iss || empty($decoded->sub) || !isset($decoded->email)) {
      return null;
    }

    // Apple identifies the user by the standard `sub` claim; there is no `user_id` claim.
    return [
      'id' => $decoded->sub,
      'email' => $decoded->email,
      // Apple sends the flag as a boolean or as the string "true"
      'email_verified' => $decoded->email_verified ?? false,
    ];
  }

  /**
   * @throws \JsonException
   */
  public static function jwt_decode(string $id_token): ?array
  {
    try {
      [$header, $payload] = explode('.', $id_token, 3);
    } catch (\Exception) {
      return null;
    }

    try {
      $decoded_header = base64_decode($header, true);
      if (false === $decoded_header) {
        return null;
      }

      $header = json_decode($decoded_header, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      return null;
    }

    // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
    $payload = str_replace(['-', '_'], ['+', '/'], $payload);
    // complete token if needed
    switch (strlen($payload) % 4) {
      case 0:
        break;

      case 2:
      case 3:
        $payload .= '=';
        break;
      default:
        return null;
    }

    $decoded_payload = base64_decode($payload, true);
    if (false === $decoded_payload) {
      return null;
    }

    $payload = json_decode($decoded_payload, true, 512, JSON_THROW_ON_ERROR);

    return ['header' => $header, 'payload' => $payload];
  }
}
