<?php

namespace App\Api;

use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\Register;

class UserApi implements UserApiInterface
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
  public function userPost(Register $register, string $acceptLanguage = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement userPost() method.
  }
}
