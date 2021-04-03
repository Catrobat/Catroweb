<?php

namespace App\Api\Services\Utility;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class UtilityApiFacade extends AbstractApiFacade
{
  private UtilityResponseManager $response_manager;
  private UtilityApiLoader $loader;
  private UtilityApiProcessor $processor;
  private UtilityRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    UtilityResponseManager $response_manager,
    UtilityApiLoader $loader,
    UtilityApiProcessor $processor,
    UtilityRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
  }

  public function getResponseManager(): UtilityResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): UtilityApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): UtilityApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): UtilityRequestValidator
  {
    return $this->request_validator;
  }
}
