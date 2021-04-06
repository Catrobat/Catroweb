<?php

namespace App\Api\Services\Notifications;

use App\Api\Services\Base\AbstractApiLoader;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationsApiLoader extends AbstractApiLoader
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }
}
