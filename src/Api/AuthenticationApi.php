<?php

namespace App\Api;

use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\APIHelper;
use CoderCat\JWKToPEM\JWKConverter;
use Exception;
use Firebase\JWT\JWT;
use Google_Client;
use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\OAuthLoginRequest;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationApi implements AuthenticationApiInterface
{
  private string $token;
  private UserManager $user_manager;
  private JWTTokenManagerInterface$jwt_manager;

  public function __construct(UserManager $user_manager, JWTTokenManagerInterface $jwt_manager)
  {
    $this->user_manager = $user_manager;
    $this->jwt_manager = $jwt_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = APIHelper::getPandaAuth($value);
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationGet(&$responseCode, array &$responseHeaders)
  {
    // Check Token is handled by LexikJWTAuthenticationBundle
    // Successful requests are passed to this method.
    $responseCode = Response::HTTP_OK;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationOauthPost(OAuthLoginRequest $o_auth_login_request, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_OK;
    $resource_owner = $o_auth_login_request->getResourceOwner();
    $resource_owner_method = 'validate'.ucfirst($resource_owner).'IdToken';
    if (!method_exists($this, $resource_owner_method))
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return new JWTResponse();
    }

    $id_token = $o_auth_login_request->getIdToken();
    $validation_response = $this->{$resource_owner_method}($id_token);
    $responseCode = $validation_response['response_code'];

    return $validation_response['token'];
  }

  /**
   * /**
   * {@inheritdoc}
   */
  public function authenticationPost(LoginRequest $login_request, &$responseCode, array &$responseHeaders)
  {
    // Login Process & token creation is handled by LexikJWTAuthenticationBundle
    // Successful requests are NOT passed to this method. This method will never be called.
    // The AuthenticationController:authenticatePostAction will only be used when Request was invalid.
    $responseCode = Response::HTTP_OK;

    return new JWTResponse();
  }

  private function validateGoogleIdToken($id_token)
  {
    $google_id = getenv('GOOGLE_ID');

    $client = new Google_Client(['client_id' => $google_id]);  // Specify the CLIENT_ID of the app that accesses the backend
    $payload = $client->verifyIdToken($id_token);
    if ($payload)
    {
      $user_id = $payload['sub'];

      $user_email = $payload['email'];
      $name = $payload['name'];
      $username = $this->createRandomUsername($name);

      return $this->connectUserToAccount($user_id, $user_email, 'google', $username);
    }
    // Invalid ID token
    $responseCode = Response::HTTP_UNAUTHORIZED;
    $token = new JWTResponse();

    return ['response_code' => $responseCode, 'token' => $token];
  }

  private function validateFacebookIdToken($id_token)
  {
    $fb_id = getenv('FB_ID');
    $public_key = getenv('FB_OAUTH_PUBLIC_KEY');
    try
    {
      $decoded = JWT::decode($id_token, $public_key, ['RS256']);
    }
    catch (Exception $e)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    if ($decoded->app_id !== $fb_id || $decoded->expires_at < time() ||
            $decoded->issued_at > time() || empty($decoded->user_id) || !isset($decoded->email)
            || !isset($decoded->name))
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    $user_id = $decoded->user_id;
    $user_email = $decoded->email;
    $name = $decoded->name;

    $username = $this->createRandomUsername($name);

    return $this->connectUserToAccount($user_id, $user_email, 'facebook', $username);
  }

  private function validateAppleIdToken($id_token)
  {
    $apple_id = getenv('APPLE_ID');

    $jwt = self::jwt_decode($id_token);
    if (!$jwt || !isset($jwt['header']['kid']))
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    $header = $jwt['header'];
    $client = new Client();
    $res = $client->request('GET', 'https://appleid.apple.com/auth/keys');
    $body = $res->getBody()->getContents();
    $keys_raw = json_decode($body, true);
    $keys = $keys_raw['keys'];
    $public_key = [];
    foreach ($keys as $key)
    {
      if ($header['kid'] === $key['kid'])
      {
        $public_key = $key;
        break;
      }
    }

    if (count($public_key) < 1)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    $jwkConverter = new JWKConverter();
    $PEM = $jwkConverter->toPEM($public_key);
    try
    {
      $decoded = JWT::decode($id_token, $PEM, ['RS256']);
    }
    catch (Exception $e)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    if ($decoded->exp < time() || $decoded->iat > time() || $decoded->aud !== $apple_id
            || 'https://appleid.apple.com' !== $decoded->iss || empty($decoded->sub) || !isset($decoded->email))
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }
    $user_id = $decoded->sub;
    $user_email = $decoded->email;

    $username = $this->createRandomUsername();

    return $this->connectUserToAccount($user_id, $user_email, 'apple', $username);
  }

  private function connectUserToAccount($user_id, $email, $resource_owner, $username)
  {
    $user = $this->user_manager->findOneBy([$resource_owner.'_id' => $user_id]);
    if ($user)
    {
      //create JWT token
      $responseCode = Response::HTTP_OK;
      $token = $this->jwt_manager->create($user);
      $token = new JWTResponse(['token' => $token]);

      return ['response_code' => $responseCode, 'token' => $token];
    }

    $user_email = $email;
    $user = $this->user_manager->findUserByEmail($user_email);
    $set_id = 'set'.ucfirst($resource_owner).'Id';
    if ($user)
    {
      $get_id = 'get'.ucfirst($resource_owner).'Id';
      if ($user->{$get_id}())
      {
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
    $user->setPassword($this->generateRandomPassword());
    $user->setOauthUser(true);
    $this->user_manager->updateUser($user);
    $responseCode = Response::HTTP_OK;
    $token = $this->jwt_manager->create($user);
    $token = new JWTResponse(['token' => $token]);

    return ['response_code' => $responseCode, 'token' => $token];
  }

  private static function jwt_decode($id_token)
  {
    try
    {
      [$header, $payload] = explode('.', $id_token, 3);
    }
    catch (Exception $e)
    {
      return null;
    }
    $header = json_decode(base64_decode($header, true), true);
    // if the token was urlencoded, do some fixes to ensure that it is valid base64 encoded
    $payload = str_replace(['-', '_'], ['+', '/'], $payload);
    // complete token if needed
    switch (\strlen($payload) % 4) {
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

  private function createRandomUsername($name = null): string
  {
    $username_base = 'user';
    if (!empty($name))
    {
      $username_base = str_replace(' ', '', $name);
    }
    $username = $username_base;
    $user_number = 0;
    while (null !== $this->user_manager->findUserByUsername($username))
    {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }

  private function generateRandomPassword(int $length = 32): string
  {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789-=~!@#$%&*()_+,.<>?;:[]{}|';

    $pass = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; ++$i)
    {
      $pass .= $chars[random_int(0, $max)];
    }

    return $pass;
  }
}
