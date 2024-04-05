<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly UserResponseManager $response_manager,
    private readonly UserApiLoader $loader,
    private readonly UserApiProcessor $processor,
    private readonly UserRequestValidator $request_validator,
    private readonly EventDispatcherInterface $event_dispatcher
  ) {
    parent::__construct($authentication_manager);
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

  public function getEventDispatcher(): EventDispatcherInterface
  {
    return $this->event_dispatcher;
  }
}
