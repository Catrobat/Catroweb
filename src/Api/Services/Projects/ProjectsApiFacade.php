<?php

namespace App\Api\Services\Projects;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class ProjectsApiFacade extends AbstractApiFacade
{
  private ProjectsResponseManager $response_manager;
  private ProjectsApiLoader $loader;
  private ProjectsApiProcessor $processor;
  private ProjectsRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    ProjectsResponseManager $response_manager,
    ProjectsApiLoader $loader,
    ProjectsApiProcessor $processor,
    ProjectsRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
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
}
