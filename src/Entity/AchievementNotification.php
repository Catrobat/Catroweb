<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AchievementNotification extends CatroNotification
{
  /**
   * @ORM\Column(name="image_path", type="text", nullable=true)
   */
  private ?string $image_path = null;

  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private string $twig_template = 'Notifications/NotificationTypes/achievement_notification.html.twig';

  public function __construct(User $user, string $title, string $message, ?string $image_path)
  {
    parent::__construct($user, $title, $message, 'achievement');
    $this->image_path = $image_path;
  }

  public function getImagePath(): ?string
  {
    return $this->image_path;
  }

  public function setImagePath(?string $image_path): void
  {
    $this->image_path = $image_path;
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
