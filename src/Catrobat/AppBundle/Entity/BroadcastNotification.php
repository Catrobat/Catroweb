<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BroadcastNotification extends CatroNotification
{

  /**
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "/components/notifications/broadcast_notification.html.twig";

  /**
   * BroadcastNotification constructor.
   *
   * @param User $user
   * @param      $title
   * @param      $message
   *
   */
  public function __construct(User $user, $title, $message)
  {
    parent::__construct($user, $title, $message);
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