<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class ReactionsApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly ReactionsResponseManager $response_manager,
    private readonly ReactionsApiLoader $loader,
    private readonly ReactionsApiProcessor $processor,
    private readonly ReactionsRequestValidator $request_validator,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): ReactionsResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): ReactionsApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): ReactionsApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): ReactionsRequestValidator
  {
    return $this->request_validator;
  }
}
