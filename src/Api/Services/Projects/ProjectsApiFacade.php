<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProjectsApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly ProjectsResponseManager $response_manager,
    private readonly ProjectsApiLoader $loader,
    private readonly ProjectsApiProcessor $processor,
    private readonly ProjectsRequestValidator $request_validator,
    private readonly EventDispatcherInterface $event_dispatcher
  ) {
    parent::__construct($authentication_manager);
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
