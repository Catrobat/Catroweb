<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserCommentRepository")
 * @ORM\Table(name="user_comment")
 */
class UserComment
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @ORM\Column(type="integer")
   */
  protected ?int $id = null;

  /**
   * The User who wrote this UserComment. If this User gets deleted, this UserComment gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\User",
   *     inversedBy="comments"
   * )
   * @ORM\JoinColumn(
   *     name="user_id",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  protected ?User $user = null;

  /**
   * The CommentNotification triggered by creating this UserComment.
   * If this UserComment gets deleted, this CommentNotification gets deleted as well.
   *
   * @ORM\OneToOne(
   *     targetEntity="\App\Entity\CommentNotification",
   *     mappedBy="comment",
   *     cascade={"remove"}
   * )
   * @ORM\JoinColumn(
   *     name="notification_id",
   *     referencedColumnName="id",
   *     onDelete="SET NULL",
   *     nullable=true
   * )
   */
  protected ?CommentNotification $notification = null;

  /**
   * @ORM\Column(type="date")
   */
  protected ?DateTime $uploadDate = null;

  /**
   * @ORM\Column(type="text")
   */
  protected ?string $text = null;

  /**
   * @ORM\Column(type="string")
   */
  protected ?string $username = null;

  /**
   * @ORM\Column(type="boolean")
   */
  protected bool $isReported = false;

  /**
   * @var Program The Program which this UserComment comments. If this Program gets deleted, this UserComment gets deleted
   *              as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity="\App\Entity\Program",
   *     inversedBy="comments"
   * )
   * @ORM\JoinColumn(
   *     name="programId",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private Program $program;

  public function __toString(): string
  {
    return $this->text ?? '';
  }

  /**
   * Returns the Program which this UserComment comments.
   */
  public function getProgram(): Program
  {
    return $this->program;
  }

  /**
   * Sets the Program which this UserComment comments.
   */
  public function setProgram(Program $program): void
  {
    $this->program = $program;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  /**
   * Returns the User who wrote this UserComment.
   */
  public function getUser(): ?User
  {
    return $this->user;
  }

  /**
   * Sets the User who wrote this UserComment.
   */
  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  public function getUploadDate(): ?DateTime
  {
    return $this->uploadDate;
  }

  public function setUploadDate(DateTime $uploadDate): void
  {
    $this->uploadDate = $uploadDate;
  }

  public function getText(): ?string
  {
    return $this->text;
  }

  public function setText(string $text): void
  {
    $this->text = $text;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setUsername(string $username): void
  {
    $this->username = $username;
  }

  public function getIsReported(): bool
  {
    return $this->isReported;
  }

  public function setIsReported(bool $isReported): void
  {
    $this->isReported = $isReported;
  }

  /**
   * Returns the CommentNotification triggered by creating this UserComment.
   */
  public function getNotification(): ?CommentNotification
  {
    return $this->notification;
  }

  /**
   * Sets the CommentNotification triggered by creating this UserComment.
   */
  public function setNotification(CommentNotification $notification): void
  {
    $this->notification = $notification;
  }
}
