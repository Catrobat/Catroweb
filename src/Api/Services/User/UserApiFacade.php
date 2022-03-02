<?php

namespace App\Api\Services\User;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UserApiFacade extends AbstractApiFacade
{
  private UserResponseManager $response_manager;
  private UserApiLoader $loader;
  private UserApiProcessor $processor;
  private UserRequestValidator $request_validator;
  private EventDispatcherInterface $event_dispatcher;

  public function __construct(
    AuthenticationManager $authentication_manager,
    UserResponseManager $response_manager,
    UserApiLoader $loader,
    UserApiProcessor $processor,
    UserRequestValidator $request_validator,
    EventDispatcherInterface $event_dispatcher
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
    $this->event_dispatcher = $event_dispatcher;
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
