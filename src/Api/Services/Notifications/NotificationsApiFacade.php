<?php

declare(strict_types=1);

namespace App\Api\Services\Notifications;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use App\DB\EntityRepository\User\Notification\NotificationRepository;

class NotificationsApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly NotificationsResponseManager $response_manager,
    private readonly NotificationsApiLoader $loader,
    private readonly NotificationsApiProcessor $processor,
    private readonly NotificationsRequestValidator $request_validator,
    private readonly NotificationRepository $notification_repository,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): NotificationsResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): NotificationsApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): NotificationsApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): NotificationsRequestValidator
  {
    return $this->request_validator;
  }

  public function getNotificationRepository(): NotificationRepository
  {
    return $this->notification_repository;
  }
}
