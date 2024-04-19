<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class AuthenticationApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly AuthenticationResponseManager $response_manager,
    private readonly AuthenticationApiLoader $loader,
    private readonly AuthenticationApiProcessor $processor,
    private readonly AuthenticationRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
  }

  public function getResponseManager(): AuthenticationResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): AuthenticationApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): AuthenticationApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): AuthenticationRequestValidator
  {
    return $this->request_validator;
  }
}
