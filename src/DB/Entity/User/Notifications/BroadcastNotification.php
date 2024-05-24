<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class BroadcastNotification extends CatroNotification
{
  /**
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/broadcast_notification.html.twig';

  public function __construct(User $user, string $title, string $message)
  {
    parent::__construct($user, $title, $message, 'broadcast');
  }

  /**
   * It's important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
