<?php

namespace App\Api;

use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\Login;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationApi implements AuthenticationApiInterface
{
  /**
   * @var string
   */
  private $token;

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value)
  {
    $this->token = $value;
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
  public function authenticationPost(Login $login, &$responseCode, array &$responseHeaders)
  {
    // Login Process & token creation is handled by LexikJWTAuthenticationBundle
    // Successful requests are NOT passed to this method. This method will never be called.
    // The AuthenticationController:authenticatePostAction will only be used when Request was invalid.
    $responseCode = Response::HTTP_OK;
  }
}
