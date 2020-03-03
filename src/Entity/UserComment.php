<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserCommentRepository")
 * @ORM\Table(name="user_comment")
 */
class UserComment
{
  /**
   *
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @ORM\Column(type="integer")
   */
  protected $id;

  /**
   * @var Program The Program which this UserComment comments. If this Program gets deleted, this UserComment gets deleted
   *              as well.
   *
   * @ORM\ManyToOne(
   *   targetEntity="\App\Entity\Program",
   *   inversedBy="comments"
   * )
   * @ORM\JoinColumn(
   *   name="programId",
   *   referencedColumnName="id",
   *   nullable=true
   *  )
   */
  private $program;

  /**
   * @var User The User who wrote this UserComment. If this User gets deleted, this UserComment gets deleted as well.
   *
   * @ORM\ManyToOne(
   *   targetEntity="\App\Entity\User",
   *   inversedBy="comments"
   * )
   * @ORM\JoinColumn(
   *   name="user_id",
   *   referencedColumnName="id",
   *   nullable=true
   * )
   */
  protected $user;

  /**
   * @var CommentNotification The CommentNotification triggered by creating this UserComment. If this UserComment
   *                          gets deleted, this CommentNotification gets deleted as well.
   *
   * @ORM\OneToOne(
   *   targetEntity="\App\Entity\CommentNotification",
   *   mappedBy="comment",
   *   cascade={"remove"}
   * )
   * @ORM\JoinColumn(
   *   name="notification_id",
   *   referencedColumnName="id",
   *   onDelete="SET NULL",
   *   nullable=true
   *  )
   */
  protected $notification;

  /**
   * @ORM\Column(type="date")
   */
  protected $uploadDate;

  /**
   * @ORM\Column(type="text")
   */
  protected $text;

  /**
   * @ORM\Column(type="string")
   */
  protected $username;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isReported;

  /**
   * Returns the Program which this UserComment comments.
   *
   * @return Program The Program which this UserComment comments.
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Sets the Program which this UserComment comments.
   *
   * @param Program $program The Program which this UserComment comments.
   */
  public function setProgram($program)
  {
    $this->program = $program;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Returns the User who wrote this UserComment.
   *
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Sets the User who wrote this UserComment.
   *
   * @param User $user The User who wrote this UserComment.
   */
  public function setUser($user)
  {
    $this->user = $user;
  }

  /**
   * @return mixed
   */
  public function getUploadDate()
  {
    return $this->uploadDate;
  }

  /**
   * @param mixed $uploadDate
   */
  public function setUploadDate($uploadDate)
  {
    $this->uploadDate = $uploadDate;
  }

  /**
   * @return mixed
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * @param mixed $text
   */
  public function setText($text)
  {
    $this->text = $text;
  }

  /**
   * @return mixed
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @param mixed $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @return mixed
   */
  public function getIsReported()
  {
    return $this->isReported;
  }

  /**
   * @param mixed $isReported
   */
  public function setIsReported($isReported)
  {
    $this->isReported = $isReported;
  }

  /**
   * Returns the CommentNotification triggered by creating this UserComment.
   *
   * @return CommentNotification The CommentNotification triggered by creating this UserComment.
   */
  public function getNotification()
  {
    return $this->notification;
  }

  /**
   * Sets the CommentNotification triggered by creating this UserComment.
   *
   * @param CommentNotification $notification The CommentNotification triggered by creating this UserComment.
   */
  public function setNotification($notification)
  {
    $this->notification = $notification;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->text;
  }
}