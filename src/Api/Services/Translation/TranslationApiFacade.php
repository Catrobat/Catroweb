<?php

declare(strict_types=1);

namespace App\Api\Services\Translation;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class TranslationApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly TranslationResponseManager $response_manager,
    private readonly TranslationApiLoader $loader,
    private readonly TranslationApiProcessor $processor,
    private readonly TranslationRequestValidator $request_validator,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): TranslationResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): TranslationApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): TranslationApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): TranslationRequestValidator
  {
    return $this->request_validator;
  }
}
