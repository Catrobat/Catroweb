<?php

namespace App\Api\Services\Base;

use App\Api\Services\AuthenticationManager;

/**
 * Class AbstractApiFacade.
 */
abstract class AbstractApiFacade implements ApiFacadeInterface
{
  protected AuthenticationManager $authentication_manager;

  public function __construct(AuthenticationManager $authentication_manager)
  {
    $this->authentication_manager = $authentication_manager;
  }

  public function getAuthenticationManager(): AuthenticationManager
  {
    return $this->authentication_manager;
  }
}
