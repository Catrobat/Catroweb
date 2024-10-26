<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StudioApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly StudioResponseManager $response_manager,
    private readonly StudioApiLoader $loader,
    private readonly StudioApiProcessor $processor,
    private readonly StudioRequestValidator $request_validator,
    private readonly ParameterBagInterface $parameter_bag,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): StudioResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): StudioApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): StudioApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): StudioRequestValidator
  {
    return $this->request_validator;
  }

  public function getParameterBag(): ParameterBagInterface
  {
    return $this->parameter_bag;
  }
}
