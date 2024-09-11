<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Security\PasswordGenerator;
use App\User\UserManager;
use CoderCat\JWKToPEM\Exception\Base64DecodeException;
use CoderCat\JWKToPEM\Exception\JWKConverterException;
use CoderCat\JWKToPEM\JWKConverter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly UserManager $user_manager, private readonly AuthenticationManager $authentication_manager)
  {
  }

  public function createJWTByUser(User $user): string
  {
    return $this->authentication_manager->createAuthenticationTokenFromUser($user);
  }

  public function createRefreshTokenByUser(User $user): string
  {
    return $this->authentication_manager->createRefreshTokenByUser($user);
  }

  /**
   * used in connectUserToAccount!
   */
  protected function getPayloadFromGoogleIdToken(mixed $id_token): array
  {
    $client = new \Google\Client(['client_id' => getenv('GOOGLE_ID')]);

    $payload = $client->verifyIdToken($id_token);

    return [
      'id' => $payload['sub'],
      'email' => $payload['email'],
      'name' => $payload['name'],
    ];
  }

  /**
   * used in connectUserToAccount!
   */
  protected function getPayloadFromFacebookIdToken(mixed $id_token): array
  {
    $public_key = $_ENV['FB_OAUTH_PUBLIC_KEY'] ?? '';
    $payload = JWT::decode($id_token, new Key($public_key, 'RS256'));

    return [
      'id' => $payload->user_id,
      'email' => $payload->email,
      'name' => $payload->name,
    ];
  }

  /**
   * used in connectUserToAccount!
   *
   * @throws GuzzleException
   * @throws Base64DecodeException
   * @throws JWKConverterException
   * @throws \JsonException
   */
  protected function getPayloadFromAppleIdToken(string $id_token): array
  {
    $jwt = AuthenticationRequestValidator::jwt_decode($id_token);

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

    $jwkConverter = new JWKConverter();
    $PEM = $jwkConverter->toPEM($public_key);
    $payload = JWT::decode($id_token, new Key($PEM, 'RS256'));

    return [
      'id' => $payload->user_id,
      'email' => $payload->email,
    ];
  }

  /**
   * @throws \Exception
   */
  public function connectUserToAccount(string $id_token, string $resource_owner): User
  {
    // Dynamic methods: setAppleId, setGoogleId, setFacebookId
    $set_id = 'set'.ucfirst($resource_owner).'Id';

    // Dynamic methods: getPayloadFromAppleIdToken, getPayloadFromGoogleIdToken, getPayloadFromFacebookIdToken
    $getPayloadMethod = 'getPayloadFrom'.ucfirst($resource_owner).'IdToken';

    // Extract payload
    $payload = $this->{$getPayloadMethod}($id_token);
    $user_id = $payload['id'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';

    /** @var User|null $user */
    $user = $this->user_manager->findOneBy([$resource_owner.'_id' => $user_id]);
    if ($user) {
      // User already exists and is already connected to this service
      return $user;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($email);
    if ($user) {
      // User already exists but is not connected to this service
      $user->{$set_id}($user_id);
      $this->user_manager->updateUser($user);

      return $user;
    }

    // User does not exist yet
    $user = $this->user_manager->create();
    $user->{$set_id}($user_id);
    $user->setEnabled(true);
    $user->setEmail($email);
    $user->setUsername($this->createRandomUsername($name));
    $user->setPassword(PasswordGenerator::generateRandomPassword());
    $user->setOauthUser(true);
    $user->setVerified(true);

    $this->user_manager->updateUser($user);

    return $user;
  }

  public function deleteRefreshToken(string $x_refresh): bool
  {
    return $this->authentication_manager->deleteRefreshToken($x_refresh);
  }

  protected function createRandomUsername(?string $name = null): string
  {
    $username_base = 'user';
    if (null !== $name && '' !== $name && '0' !== $name) {
      $username_base = str_replace(' ', '', $name);
    }

    $username = $username_base;
    $user_number = 0;
    while ($this->user_manager->findUserByUsername($username) instanceof UserInterface) {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }
}
