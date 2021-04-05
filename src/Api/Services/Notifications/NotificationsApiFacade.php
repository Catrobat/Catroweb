<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

final class NotificationsApiFacade extends AbstractApiFacade
{
  private NotificationsResponseManager $response_manager;
  private NotificationsApiLoader $loader;
  private NotificationsApiProcessor $processor;
  private NotificationsRequestValidator $request_validator;

  public function __construct(
    AuthenticationManager $authentication_manager,
    NotificationsResponseManager $response_manager,
    NotificationsApiLoader $loader,
    NotificationsApiProcessor $processor,
    NotificationsRequestValidator $request_validator
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
  }

  public function getResponseManager(): NotificationsResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): NotificationsApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): NotificationsApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): NotificationsRequestValidator
  {
    return $this->request_validator;
  }
}
