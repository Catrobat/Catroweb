<?php

namespace App\DB\Entity\User\Comment;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserCommentRepository::class)
 *
 * @ORM\Table(
 *     name="user_comment",
 *     indexes={
 *
 *         @ORM\Index(name="parent_id_idx", columns={"parent_id"}),
 *         @ORM\Index(name="user_id_idx", columns={"user_id"}),
 *         @ORM\Index(name="project_id_idx", columns={"projectId"}),
 *         @ORM\Index(name="studio_idx", columns={"studio"}),
 *         @ORM\Index(name="upload_date_idx", columns={"uploadDate"})
 *     }
 * )
 */
class UserComment implements \Stringable
{
  /**
   * @ORM\Id
   *
   * @ORM\GeneratedValue(strategy="AUTO")
   *
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
   *
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
   *
   * @ORM\JoinColumn(
   *     name="notification_id",
   *     referencedColumnName="id",
   *     onDelete="SET NULL",
   *     nullable=true
   * )
   */
  protected ?CommentNotification $notification = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?\DateTime $uploadDate = null;

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
   * The Project which this UserComment comments. If this Project gets deleted, this UserComment gets deleted as well.
   *
   * @ORM\ManyToOne(
   *     targetEntity=Project::class,
   *     inversedBy="comments"
   * )
   *
   * @ORM\JoinColumn(
   *     name="projectId",
   *     referencedColumnName="id",
   *     nullable=true
   * )
   */
  private ?Project $project = null;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected ?int $parent_id = null;

  /**
   * @ORM\ManyToOne(targetEntity=Studio::class, inversedBy="user_comments", cascade={"persist"})
   *
   * @ORM\JoinColumn(name="studio", referencedColumnName="id", nullable=true, onDelete="CASCADE")
   */
  protected ?Studio $studio = null;

  /**
   * @ORM\Column(type="boolean", nullable=false, options={"default": false})
   */
  protected bool $is_deleted = false;

  /**
   * @ORM\OneToOne(targetEntity=StudioActivity::class, cascade={"persist"})
   *
   * @ORM\JoinColumn(name="activity", referencedColumnName="id", nullable=true, onDelete="CASCADE")
   */
  protected ?StudioActivity $activity = null;

  /**
   * Only on demand, not in database. (currently).
   */
  public ?int $number_of_replies = null;

  public function __toString(): string
  {
    return $this->text ?? '';
  }

  /**
   * Returns the Project which this UserComment comments.
   */
  public function getProject(): ?Project
  {
    return $this->project;
  }

  /**
   * Sets the Project which this UserComment comments.
   */
  public function setProject(Project $project): UserComment
  {
    $this->project = $project;

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

  public function getUploadDate(): ?\DateTime
  {
    return $this->uploadDate;
  }

  public function setUploadDate(\DateTime $uploadDate): UserComment
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

  public function getIsDeleted(): ?bool
  {
    return $this->is_deleted;
  }

  public function setIsDeleted(?bool $is_deleted): UserComment
  {
    $this->is_deleted = $is_deleted;

    return $this;
  }

  public function getNumberOfReplies(): ?int
  {
    return $this->number_of_replies;
  }

  /**
   * @return $this
   */
  public function setNumberOfReplies(?int $number_of_replies): self
  {
    $this->number_of_replies = $number_of_replies;

    return $this;
  }
}
