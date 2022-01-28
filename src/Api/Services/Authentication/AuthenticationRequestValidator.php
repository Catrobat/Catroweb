<?php

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Manager\UserManager;
use CoderCat\JWKToPEM\JWKConverter;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthenticationRequestValidator extends AbstractRequestValidator
{
  private UserManager $user_manager;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, UserManager $user_manager)
  {
    parent::__construct($validator, $translator);
    $this->user_manager = $user_manager;
  }

  public function validateGoogleIdToken(string $id_token): bool
  {
    // Specify the CLIENT_ID of the app that accesses the backend
    $client = new \Google\Client(['client_id' => getenv('GOOGLE_ID')]);

    $payload = $client->verifyIdToken($id_token);

    return boolval($payload);
  }

  public function validateFacebookIdToken(string $id_token): bool
  {
    try {
      $public_key = getenv('FB_OAUTH_PUBLIC_KEY');
      $decoded = JWT::decode($id_token, $public_key, ['RS256']);
    } catch (Exception $e) {
      return false;
    }

    if ($decoded->app_id !== getenv('FB_ID') || $decoded->expires_at < time()
      || $decoded->issued_at > time() || empty($decoded->user_id) || !isset($decoded->email)
      || !isset($decoded->name)) {
      return false;
    }

    return true;
  }

  public function validateAppleIdToken(string $id_token): bool
  {
    $jwt = $this->jwt_decode($id_token);
    if (!$jwt || !isset($jwt['header']['kid'])) {
      return false;
    }

    $header = $jwt['header'];
    $client = new Client();
    $res = $client->request('GET', 'https://appleid.apple.com/auth/keys');
    $body = $res->getBody()->getContents();
    $keys_raw = json_decode($body, true);
    $keys = $keys_raw['keys'];
    $public_key = [];
    foreach ($keys as $key) {
      if ($header['kid'] === $key['kid']) {
        $public_key = $key;
        break;
      }
    }

    if (count($public_key) < 1) {
      return false;
    }

    $jwkConverter = new JWKConverter();

    try {
      $PEM = $jwkConverter->toPEM($public_key);
      $decoded = JWT::decode($id_token, $PEM, ['RS256']);
    } catch (Exception $e) {
      return false;
    }

    if ($decoded->exp < time() || $decoded->iat > time() || $decoded->aud !== getenv('APPLE_ID')
      || 'https://appleid.apple.com' !== $decoded->iss || empty($decoded->sub) || !isset($decoded->email)) {
      return false;
    }

    return true;
  }

  public static function jwt_decode(string $id_token): ?array
  {
    try {
      [$header, $payload] = explode('.', $id_token, 3);
    } catch (Exception $e) {
      return null;
    }
    $header = json_decode(base64_decode($header, true), true);
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
    $payload = json_decode(base64_decode($payload, true), true);

    return ['header' => $header, 'payload' => $payload];
  }
}
