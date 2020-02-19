<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FollowNotification extends CatroNotification
{
  /**
   * @var User The User which "follow action" to another user triggered this FollowNotification. If this user gets deleted,
   *           this FollowNotification gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\User",
   *     inversedBy="follow_notification_mentions"
   * )
   * @ORM\JoinColumn(
   *     name="follower_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private $follower;

  /**
   *  You have to set this parameter otherwise the wrong template will be rendered.
   */
  private $twig_template = '/Notifications/NotificationTypes/follow_notification.html.twig';

  /**
   * FollowNotification constructor.
   *
   * @param User $user    the User to which this FollowNotification should be shown
   * @param User $profile the User which "follow action" to another user triggered this FollowNotification
   */
  public function __construct(User $user, $profile)
  {
    parent::__construct($user);
    $this->follower = $profile;
  }

  /**
   * Returns the User which "follow action" to another user triggered this FollowNotification.
   *
   * @return User the User which "follow action" to another user triggered this FollowNotification
   */
  public function getFollower()
  {
    return $this->follower;
  }

  /**
   * Sets the User which "follow action" to another user triggered this FollowNotification.
   *
   * @param User $follower the User which "follow action" to another user triggered this FollowNotification
   */
  public function setFollower($follower)
  {
    $this->follower = $follower;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered.
   *
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }
}
