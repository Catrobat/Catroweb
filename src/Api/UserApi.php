<?php

namespace App\Api;

use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\Register;

class UserApi implements UserApiInterface
{
  private string $token;

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value)
  {
    $this->token = preg_split('/\s+/', $value)[1];
  }

  /**
   * {@inheritdoc}
   */
  public function userPost(Register $register, ?string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement userPost() method.
  }
}
