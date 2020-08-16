<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AnniversaryNotification extends CatroNotification
{
  /**
   * @ORM\Column(name="prize", type="text")
   */
  private string $prize;

  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/anniversary_notification.html.twig';

  public function __construct(User $user, string $title, string $message, string $prize)
  {
    parent::__construct($user, $title, $message, 'anniversary');
    $this->prize = $prize;
  }

  public function getPrize(): string
  {
    return $this->prize;
  }

  public function setPrize(string $prize): void
  {
    $this->prize = $prize;
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
