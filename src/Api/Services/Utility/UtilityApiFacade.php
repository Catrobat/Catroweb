<?php

declare(strict_types=1);

namespace App\Api\Services\Utility;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class UtilityApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly UtilityResponseManager $response_manager,
    private readonly UtilityApiLoader $loader,
    private readonly UtilityApiProcessor $processor,
    private readonly UtilityRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): UtilityResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): UtilityApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): UtilityApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): UtilityRequestValidator
  {
    return $this->request_validator;
  }
}
