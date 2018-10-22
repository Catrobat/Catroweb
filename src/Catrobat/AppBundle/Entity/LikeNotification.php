<?php
/**
 * Copyright (c) 2018. Catrobat
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
 * @ORM\Entity
 */
class LikeNotification extends CatroNotification
{
  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="like_from", referencedColumnName="id", nullable=true)
   */
  private $like_from;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   * @var \Catrobat\AppBundle\Entity\Program
   */
  private $program;

  /*
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "/components/notifications/like_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param $title
   * @param $message
   * @param $like_from
   *
   */
  public function __construct(User $user, $title, $message, $like_from, $program)
  {
    parent::__construct($user, $title, $message);
    $this->like_from = $like_from;
    $this->program = $program;
    /* if you didn't forget to set the member variable to default above
       you don't need the following line */
    $this->twig_template = "/components/notifications/like_notification.html.twig";
  }

  /**
   * @return mixed
   */
  public function getLikeFrom()
  {
    return $this->like_from;
  }

  /**
   * @param $like_from
   */
  public function setLikeFrom($like_from)
  {
    $this->like_from = $like_from;
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

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @param Program $program
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }


}