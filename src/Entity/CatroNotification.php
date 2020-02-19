<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 *
 * @ORM\Table
 */

/**
 * @ORM\Entity(repositoryClass="App\Repository\CatroNotificationRepository")
 * @ORM\Table
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "default": "CatroNotification",
 *     "anniversary": "AnniversaryNotification",
 *     "achievement": "AchievementNotification",
 *     "comment": "CommentNotification",
 *     "like": "LikeNotification",
 *     "follow": "FollowNotification",
 *     "follow_program": "NewProgramNotification",
 *     "broadcast_notification": "BroadcastNotification",
 *     "remix_notification": "RemixNotification"
 * })
 */
class CatroNotification
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var User The user to which this CatroNotification will be shown. If the user gets deleted, this CatroNotification
   *           gets deleted as well.
   *
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="notifications")
   * @ORM\JoinColumn(
   *     name="user",
   *     referencedColumnName="id",
   *     nullable=false
   * )
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
  /**
   * @ORM\Column(name="seen", type="boolean", options={"default": false})
   */
  private $seen = false;

  private $twig_template = 'Notifications/NotificationTypes/catro_notification.html.twig';

  /**
   * CatroNotification constructor.
   *
   * @param $title
   * @param $message
   */
  public function __construct(User $user, $title = '', $message = '')
  {
    $this->user = $user;
    $this->title = $title;
    $this->message = $message;
  }

  /**
   * Get notification id.
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set id.
   *
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Set title.
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
   * Get title.
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set seen.
   *
   * @param bool $seen
   *
   * @return CatroNotification
   */
  public function setSeen($seen)
  {
    $this->seen = $seen;

    return $this;
  }

  /**
   * Get seen.
   *
   * @return bool
   */
  public function getSeen()
  {
    return $this->seen;
  }

  /**
   * Set message.
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
   * Get message.
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Sets he user to which this CatroNotification will be shown.
   *
   * @param User $user the user to which this CatroNotification will be shown
   *
   * @return CatroNotification
   */
  public function setUser(User $user)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Returns the user to which this CatroNotification will be shown.
   *
   * @return User
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
