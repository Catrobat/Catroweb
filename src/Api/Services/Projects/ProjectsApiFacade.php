<?php

namespace App\Api\Services\Projects;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ProjectsApiFacade extends AbstractApiFacade
{
  private ProjectsResponseManager $response_manager;
  private ProjectsApiLoader $loader;
  private ProjectsApiProcessor $processor;
  private ProjectsRequestValidator $request_validator;
  private EventDispatcherInterface $event_dispatcher;

  public function __construct(
    AuthenticationManager $authentication_manager,
    ProjectsResponseManager $response_manager,
    ProjectsApiLoader $loader,
    ProjectsApiProcessor $processor,
    ProjectsRequestValidator $request_validator,
    EventDispatcherInterface $event_dispatcher
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
    $this->event_dispatcher = $event_dispatcher;
  }

  public function getResponseManager(): ProjectsResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): ProjectsApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): ProjectsApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): ProjectsRequestValidator
  {
    return $this->request_validator;
  }

  public function getEventDispatcher(): EventDispatcherInterface
  {
    return $this->event_dispatcher;
  }
}
