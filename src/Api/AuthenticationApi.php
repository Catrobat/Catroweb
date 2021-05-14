<?php

namespace App\Api;

use App\Api\Services\Authentication\AuthenticationApiFacade;
use App\Api\Services\Base\AbstractApiController;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\OAuthLoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use OpenAPI\Server\Model\UpgradeTokenRequest;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticationApi extends AbstractApiController implements AuthenticationApiInterface
{
  private AuthenticationApiFacade $facade;

  public function __construct(AuthenticationApiFacade $facade)
  {
    $this->facade = $facade;
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

  /**
   * {@inheritdoc}
   */
  public function authenticationDelete(string $x_refresh, &$responseCode, array &$responseHeaders)
  {
    if ($this->facade->getProcessor()->deleteRefreshToken($x_refresh)) {
      $responseCode = Response::HTTP_OK;
      return;
    }
    $responseCode = Response::HTTP_UNAUTHORIZED;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationRefreshPost(RefreshRequest $refresh_request, &$responseCode, array &$responseHeaders)
  {
    //TODO: Implement authenticationRefreshPost() method

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return new JWTResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationOauthPost(OAuthLoginRequest $o_auth_login_request, &$responseCode, array &$responseHeaders)
  {
    $resource_owner = $o_auth_login_request->getResourceOwner() ?? '';
    $id_token = $o_auth_login_request->getIdToken() ?? '';

    $resource_owner_method = 'validate'.ucfirst($resource_owner).'IdToken';

    if (!method_exists($this->facade->getRequestValidator(), $resource_owner_method)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return new JWTResponse();
    }

    $validation_response = $this->facade->getRequestValidator()->{$resource_owner_method}($id_token);

    if (!$validation_response) {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }

    $response = $this->facade->getProcessor()->connectUserToAccount($id_token, $resource_owner);

    $responseCode = $response['response_code'];

    return $response['token'];
  }

  public function authenticationUpgradePost(UpgradeTokenRequest $upgrade_token_request, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement authenticationUpgradePost() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }
}
