<?php

declare(strict_types=1);

namespace App\Api\Services\Moderation;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class ModerationApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly ModerationResponseManager $response_manager,
    private readonly ModerationApiLoader $loader,
    private readonly ModerationApiProcessor $processor,
    private readonly ModerationRequestValidator $request_validator,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): ModerationResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): ModerationApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): ModerationApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): ModerationRequestValidator
  {
    return $this->request_validator;
  }
}
