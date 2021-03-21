<?php

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class MediaLibraryApiFacade extends AbstractApiFacade
{
  private MediaLibraryResponseManager $response_manager;
  private MediaLibraryApiLoader $loader;
  private MediaLibraryApiProcessor $processor;
  private MediaLibraryRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    MediaLibraryResponseManager $response_manager,
    MediaLibraryApiLoader $loader,
    MediaLibraryApiProcessor $processor,
    MediaLibraryRequestValidator $request_validator)
  {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
  }

  public function getResponseManager(): MediaLibraryResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): MediaLibraryApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): MediaLibraryApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): MediaLibraryRequestValidator
  {
    return $this->request_validator;
  }
}
