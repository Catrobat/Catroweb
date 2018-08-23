<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 */
class AnniversaryNotification extends CatroNotification
{
  /**
   * @ORM\Column(name="prize", type="text")
   */
  private $prize;

  /*
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "components/notifications/anniversary_notification.html.twig";

  /**
   * AnniversaryNotification constructor.
   *
   * @param User $user
   * @param $title
   * @param $message
   * @param $prize
   */
  public function __construct(User $user, $title, $message, $prize)
  {
    parent::__construct($user, $title, $message);
    $this->prize = $prize;
    /* if you didn't forget to set the member variable to default above
       you don't need the following line */
    $this->twig_template = "components/notifications/anniversary_notification.html.twig";
  }

  /**
   * @return mixed
   */
  public function getPrize()
  {
    return $this->prize;
  }

  /**
   * @param mixed $prize
   */
  public function setPrize($prize)
  {
    $this->prize = $prize;
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