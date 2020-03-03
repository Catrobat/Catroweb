<?php

namespace App\Api;

use OpenAPI\Server\Api\SecurityApiInterface;
use OpenAPI\Server\Model\Login;
use OpenAPI\Server\Model\Logout;
use OpenAPI\Server\Model\Register;
use OpenAPI\Server\Model\UsernameObject;
use Symfony\Component\HttpFoundation\Response;

class SecurityApi implements SecurityApiInterface
{
  /**
   * {@inheritdoc}
   */
  public function checkTokenPost($token, UsernameObject $usernameObject, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement checkTokenPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function loginPost(Login $login, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement loginPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function logoutPost($token, Logout $logout, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement logoutPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function registerUserPost(Register $register, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement registerUserPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function registerValidationPost(Register $register, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement registerValidationPost() method.
  }
}
