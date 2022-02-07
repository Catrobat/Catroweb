<?php

namespace App\DB\Entity\User\Comment;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserCommentRepository::class)
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
   *     targetEntity=User::class,
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
   *     targetEntity=CommentNotification::class,
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
   * The Program which this UserComment comments. If this Program gets deleted, this UserComment gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity=Program::class,
   *     inversedBy="comments"
   * )
   * @ORM\JoinColumn(
   *     name="programId",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private ?Program $program = null;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected ?int $parent_id = null;

  /**
   * @ORM\ManyToOne(targetEntity=Studio::class, inversedBy="comments", cascade={"persist"})
   * @ORM\JoinColumn(name="studio", referencedColumnName="id", nullable=true, onDelete="CASCADE")
   */
  protected ?Studio $studio;

  /**
   * @ORM\OneToOne(targetEntity=StudioActivity::class, cascade={"persist"})
   * @ORM\JoinColumn(name="activity", referencedColumnName="id", nullable=true, onDelete="CASCADE")
   */
  protected ?StudioActivity $activity;

  public function __toString(): string
  {
    return $this->text ?? '';
  }

  /**
   * Returns the Program which this UserComment comments.
   */
  public function getProgram(): ?Program
  {
    return $this->program;
  }

  /**
   * Sets the Program which this UserComment comments.
   */
  public function setProgram(Program $program): UserComment
  {
    $this->program = $program;

    return $this;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): UserComment
  {
    $this->id = $id;

    return $this;
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
  public function setUser(User $user): UserComment
  {
    $this->user = $user;

    return $this;
  }

  public function getUploadDate(): ?DateTime
  {
    return $this->uploadDate;
  }

  public function setUploadDate(DateTime $uploadDate): UserComment
  {
    $this->uploadDate = $uploadDate;

    return $this;
  }

  public function getText(): ?string
  {
    return $this->text;
  }

  public function setText(string $text): UserComment
  {
    $this->text = $text;

    return $this;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setUsername(string $username): UserComment
  {
    $this->username = $username;

    return $this;
  }

  public function getIsReported(): bool
  {
    return $this->isReported;
  }

  public function setIsReported(bool $isReported): UserComment
  {
    $this->isReported = $isReported;

    return $this;
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
  public function setNotification(CommentNotification $notification): UserComment
  {
    $this->notification = $notification;

    return $this;
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(?Studio $studio): UserComment
  {
    $this->studio = $studio;

    return $this;
  }

  public function getActivity(): ?StudioActivity
  {
    return $this->activity;
  }

  public function setActivity(?StudioActivity $activity): UserComment
  {
    $this->activity = $activity;

    return $this;
  }

  public function getParentId(): ?int
  {
    return $this->parent_id;
  }

  public function setParentId(?int $parent_id): UserComment
  {
    $this->parent_id = $parent_id;

    return $this;
  }
}
