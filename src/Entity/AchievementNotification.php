<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AchievementNotification extends CatroNotification
{
  /**
   * @ORM\Column(name="image_path", type="text")
   */
  private $image_path;

  /*
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "Notifications/NotificationTypes/achievement_notification.html.twig";

  /**
   * AchievementNotification constructor.
   *
   * @param User $user
   * @param      $title
   * @param      $message
   * @param      $image_path
   *
   */
  public function __construct(User $user, $title, $message, $image_path)
  {
    parent::__construct($user, $title, $message);
    $this->image_path = $image_path;
    /* if you didn't forget to set the member variable to default above
       you don't need the following line */
    $this->twig_template = "Notifications/NotificationTypes/achievement_notification.html.twig";
  }

  /**
   * @return mixed
   */
  public function getImagePath()
  {
    return $this->image_path;
  }

  /**
   * @param $image_path
   */
  public function setImagePath($image_path)
  {
    $this->image_path = $image_path;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }


}