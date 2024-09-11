<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractRequestValidator;
use CoderCat\JWKToPEM\JWKConverter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AuthenticationRequestValidator extends AbstractRequestValidator
{
  public function validateGoogleIdToken(string $id_token): bool
  {
    try {
      // Specify the CLIENT_ID of the app that accesses the backend
      $client = new \Google\Client(['client_id' => getenv('GOOGLE_ID')]);
      $payload = $client->verifyIdToken($id_token);

      return boolval($payload);
    } catch (\Exception) {
      return false;
    }
  }

  public function validateFacebookIdToken(string $id_token): bool
  {
    try {
      $public_key = $_ENV['FB_OAUTH_PUBLIC_KEY'] ?? '';
      $decoded = JWT::decode($id_token, new Key($public_key, 'RS256'));
    } catch (\Exception) {
      return false;
    }

    if ($decoded->expires_at < time() || $decoded->issued_at > time() || empty($decoded->user_id) || !isset($decoded->email) || !isset($decoded->name)) {
      return false;
    }

    return true;
  }

  /**
   * @throws GuzzleException
   * @throws \JsonException
   */
  public function validateAppleIdToken(string $id_token): bool
  {
    $jwt = self::jwt_decode($id_token);
    if (null === $jwt || [] === $jwt || !isset($jwt['header']['kid'])) {
      return false;
    }

    $header = $jwt['header'];
    $client = new Client();
    $res = $client->request('GET', 'https://appleid.apple.com/auth/keys');
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
      return false;
    }

    $jwkConverter = new JWKConverter();

    try {
      $PEM = $jwkConverter->toPEM($public_key);
      $decoded = JWT::decode($id_token, new Key($PEM, 'RS256'));
    } catch (\Exception) {
      return false;
    }

    if ($decoded->exp < time() || $decoded->iat > time() || $decoded->aud !== getenv('APPLE_ID')
      || 'https://appleid.apple.com' !== $decoded->iss || empty($decoded->sub) || !isset($decoded->email)) {
      return false;
    }

    return true;
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
      $header = json_decode(base64_decode($header, true), true, 512, JSON_THROW_ON_ERROR);
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

    $payload = json_decode(base64_decode($payload, true), true, 512, JSON_THROW_ON_ERROR);

    return ['header' => $header, 'payload' => $payload];
  }
}
