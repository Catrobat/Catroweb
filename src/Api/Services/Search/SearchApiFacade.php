<?php

namespace App\Api\Services\Search;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class SearchApiFacade extends AbstractApiFacade
{
  private SearchResponseManager $response_manager;
  private SearchApiLoader $loader;
  private SearchApiProcessor $processor;
  private SearchRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    SearchResponseManager $response_manager,
    SearchApiLoader $loader,
    SearchApiProcessor $processor,
    SearchRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
  }

  public function getResponseManager(): SearchResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): SearchApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): SearchApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): SearchRequestValidator
  {
    return $this->request_validator;
  }
}
