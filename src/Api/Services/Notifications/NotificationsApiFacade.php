<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use App\DB\EntityRepository\User\Notification\NotificationRepository;

final class NotificationsApiFacade extends AbstractApiFacade
{
  private NotificationsResponseManager $response_manager;
  private NotificationsApiLoader $loader;
  private NotificationsApiProcessor $processor;
  private NotificationsRequestValidator $request_validator;
  private NotificationRepository $notification_repository;

  public function __construct(
    AuthenticationManager $authentication_manager,
    NotificationsResponseManager $response_manager,
    NotificationsApiLoader $loader,
    NotificationsApiProcessor $processor,
    NotificationsRequestValidator $request_validator,
    NotificationRepository $notification_repository
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;
    $this->notification_repository = $notification_repository;
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

  public function getNotificationRepository(): NotificationRepository
  {
    return $this->notification_repository;
  }
}
