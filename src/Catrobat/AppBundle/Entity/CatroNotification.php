<?php
/**
 * Copyright (c) 2017. Catrobat
 * Imagine that each Catrobat program is a cake, a very special cake that comes with its recipe (programming blocks).
 * All members of the Catrobat community share their cakes along with their recipes. This means that you can enjoy the
 * cakes and learn how to make them yourself! There are no secret recipes: the instructions on how to make these cakes
 * are open for anyone to use, reuse, modify, and serve as inspiration for new ideas... I mean cakes.
 *
 * You can eat the cakes as well as copy other people's recipes to make your own, maybe with different ingredients.
 * This freedom comes with two simple requirements:
 *
 * share your cakes along with the recipe
 * give credit to those who inspired you
 *
 *
 * In setting up the Catrobat community, we decided to adopt this approach since we believe that it supports learning
 * and creativity within the community. By sharing recipes and ingredients (scripts and artwork), people can build upon
 * one another's ideas and everyone will benefit.
 *
 * In designing the Catrobat website, we included features to encourage people to share and to give credit to others.
 * On each program page, you can always download the original scripts for the program. If you remix a program
 * (modifying the scripts or artwork, and sharing the result), we encourage you to give credit in the Program Notes,
 * mentioning the people and program that inspired you.
 *
 * Learn more about the terms of use of the Catrobat online community on https://share.catrob.at/pocketcode/termsOfUse.
 *
 * Version 1.1, 2 April 2013
 */

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 * @ORM\Table
 */

/**
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\CatroNotificationRepository")
 * @ORM\Table
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="string")
 * @ORM\DiscriminatorMap({
 *   "default" = "CatroNotification",
 *   "anniversary" = "AnniversaryNotification",
 *   "achievement" = "AchievementNotification"
 * })
 */
class CatroNotification
{
//  const DEFAULT_NOTIFICATION = 0;
//  const ANNIVERSARY = 1;
//  public function getType()
//  {
//    return CatroNotification::DEFAULT_NOTIFICATION;
//  }
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var User
   *
   * @ORM\OneToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
   */
  private $user;

  /**
   * @ORM\Column(name="title", type="string")
   */
  private $title;

  /**
   * @ORM\Column(name="message", type="text")
   */
  private $message;

  private $twig_template = ":components/notifications:catro_notification.html.twig";

  /**
   * CatroNotification constructor.
   *
   * @param User $user
   * @param $title
   * @param $message
   */
  public function __construct(User $user, $title, $message)
  {
    $this->user = $user;
    $this->title = $title;
    $this->message = $message;
  }


  public function getId()
  {
    return $this->id;
  }

  /**
   * Set title
   *
   * @param string $title
   *
   * @return CatroNotification
   */
  public function setTitle($title)
  {
    $this->title = $title;

    return $this;
  }

  /**
   * Get title
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set message
   *
   * @param string $message
   *
   * @return CatroNotification
   */
  public function setMessage($message)
  {
    $this->message = $message;

    return $this;
  }

  /**
   * Get message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Set user
   *
   * @param \Catrobat\AppBundle\Entity\User $user
   *
   * @return CatroNotification
   */
  public function setUser(\Catrobat\AppBundle\Entity\User $user)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user
   *
   * @return \Catrobat\AppBundle\Entity\User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }

  /**
   * @param mixed $twig_template
   */
  public function setTwigTemplate($twig_template)
  {
    $this->twig_template = $twig_template;
  }


}


