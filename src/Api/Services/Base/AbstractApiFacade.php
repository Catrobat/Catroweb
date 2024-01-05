<?php

namespace App\Api\Services\Base;

use App\Api\Services\AuthenticationManager;

/**
 * Class AbstractApiFacade.
 */
abstract class AbstractApiFacade implements ApiFacadeInterface
{
  public function __construct(protected AuthenticationManager $authentication_manager)
  {
  }

  public function getAuthenticationManager(): AuthenticationManager
  {
    return $this->authentication_manager;
  }
}
