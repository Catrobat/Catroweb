<?php

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   */
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
