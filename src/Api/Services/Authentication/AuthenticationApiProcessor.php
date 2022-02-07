<?php

namespace App\Api\Services\Authentication;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Security\PasswordGenerator;
use App\User\UserManager;
use CoderCat\JWKToPEM\JWKConverter;
use Firebase\JWT\JWT;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenAPI\Server\Model\JWTResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticationApiProcessor extends AbstractApiProcessor
{
  private AuthenticationManager $authentication_manager;
  private UserManager $user_manager;
  private JWTTokenManagerInterface $jwt_manager;
  private RefreshTokenManagerInterface $refresh_manager;

  public function __construct(UserManager $user_manager, JWTTokenManagerInterface $jwt_manager,
                              AuthenticationManager $authentication_manager,
                              RefreshTokenManagerInterface $refresh_manager)
  {
    $this->user_manager = $user_manager;
    $this->authentication_manager = $authentication_manager;
    $this->jwt_manager = $jwt_manager;
    $this->refresh_manager = $refresh_manager;
  }

  public function createJWTByUser(User $user): string
  {
    return $this->jwt_manager->create($user);
  }

  /**
   * used in connectUserToAccount!
   *
   * @param mixed $id_token
   */
  protected function getPayloadFromGoogleIdToken($id_token): array
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
   *
   * @param mixed $id_token
   */
  protected function getPayloadFromFacebookIdToken($id_token): array
  {
    $payload = JWT::decode($id_token, getenv('FB_OAUTH_PUBLIC_KEY'), ['RS256']);

    return [
      'id' => $payload->user_id,
      'email' => $payload->email,
      'name' => $payload->name,
    ];
  }

  /**
   * used in connectUserToAccount!
   *
   * @psalm-return array{id: mixed, email: mixed}
   */
  protected function getPayloadFromAppleIdToken(string $id_token): array
  {
    $jwt = AuthenticationRequestValidator::jwt_decode($id_token);

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

    $jwkConverter = new JWKConverter();
    $PEM = $jwkConverter->toPEM($public_key);
    $payload = JWT::decode($id_token, $PEM, ['RS256']);

    return [
      'id' => $payload->user_id,
      'email' => $payload->email,
    ];
  }

  /**
   * @psalm-return array{response_code: 200|422, token: JWTResponse}
   */
  public function connectUserToAccount(string $id_token, string $resource_owner): array
  {
    $getPayloadMethod = 'getPayloadFrom'.ucfirst($resource_owner).'IdToken';
    $payload = $this->{$getPayloadMethod}($id_token);

    $user_id = $payload['id'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $username = $this->createRandomUsername($name);

    $user = $this->user_manager->findOneBy([$resource_owner.'_id' => $user_id]);

    if ($user) {
      //create JWT token
      $responseCode = Response::HTTP_OK;
      $token = $this->jwt_manager->create($user);
      $token = new JWTResponse(['token' => $token]);

      return ['response_code' => $responseCode, 'token' => $token];
    }

    $user_email = $email;
    $user = $this->user_manager->findUserByEmail($user_email);
    $set_id = 'set'.ucfirst($resource_owner).'Id';
    if ($user) {
      $get_id = 'get'.ucfirst($resource_owner).'Id';
      if ($user->{$get_id}()) {
        $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        $token = new JWTResponse();

        return ['response_code' => $responseCode, 'token' => $token];
      }
      $user->{$set_id}($user_id);
      $token = $this->jwt_manager->create($user);
      $token = new JWTResponse(['token' => $token]);
      $this->user_manager->updateUser($user);
      $responseCode = Response::HTTP_OK;

      return ['response_code' => $responseCode, 'token' => $token];
    }

    /** @var User $user */
    $user = $this->user_manager->createUser();
    $user->{$set_id}($user_id);
    $user->setEnabled(true);
    $user->setEmail($user_email);
    $user->setUsername($username);
    $user->setPassword(PasswordGenerator::generateRandomPassword());
    $user->setOauthUser(true);
    $this->user_manager->updateUser($user);
    $responseCode = Response::HTTP_OK;
    $token = $this->jwt_manager->create($user);
    $token = new JWTResponse(['token' => $token]);

    return ['response_code' => $responseCode, 'token' => $token];
  }

  public function deleteRefreshToken(string $x_refresh): bool
  {
    $refreshToken = $this->refresh_manager->get($x_refresh);
    if (null === $refreshToken) {
      return false;
    }
    $this->refresh_manager->delete($refreshToken);

    return true;
  }

  protected function createRandomUsername($name = null): string
  {
    $username_base = 'user';
    if (!empty($name)) {
      $username_base = str_replace(' ', '', $name);
    }
    $username = $username_base;
    $user_number = 0;
    while (null !== $this->user_manager->findUserByUsername($username)) {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }
}
