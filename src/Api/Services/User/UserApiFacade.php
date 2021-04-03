<?php

namespace App\Api\Services\User;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class UserApiFacade extends AbstractApiFacade
{
  private UserResponseManager $response_manager;
  private UserApiLoader $loader;
  private UserApiProcessor $processor;
  private UserRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    UserResponseManager $response_manager,
    UserApiLoader $loader,
    UserApiProcessor $processor,
    UserRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
  }

  public function getResponseManager(): UserResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): UserApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): UserApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): UserRequestValidator
  {
    return $this->request_validator;
  }
}
