<?php

namespace App\Api;

use App\Utils\APIHelper;
use Exception;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationApi implements AuthenticationApiInterface
{
  private string $token;

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
  public function authenticationPost(LoginRequest $login_request, &$responseCode, array &$responseHeaders)
  {
    // Login Process & token creation is handled by LexikJWTAuthenticationBundle
    // Successful requests are NOT passed to this method. This method will never be called.
    // The AuthenticationController:authenticatePostAction will only be used when Request was invalid.
    $responseCode = Response::HTTP_OK;

    return new JWTResponse();
  }
}
