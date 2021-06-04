<?php

namespace App\Api\Services\Authentication;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class AuthenticationApiFacade extends AbstractApiFacade
{
  private AuthenticationResponseManager $response_manager;
  private AuthenticationApiLoader $loader;
  private AuthenticationApiProcessor $processor;
  private AuthenticationRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    AuthenticationResponseManager $response_manager,
    AuthenticationApiLoader $loader,
    AuthenticationApiProcessor $processor,
    AuthenticationRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
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
