<?php

namespace App\DB\Entity\Project;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectLikeRepository;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 *
 * @ORM\Table(name="project_like")
 *
 * @ORM\Entity(repositoryClass=ProjectLikeRepository::class)
 */
class ProjectLike implements \Stringable
{
  final public const TYPE_NONE = 0;
  final public const TYPE_THUMBS_UP = 1;
  final public const TYPE_SMILE = 2;
  final public const TYPE_LOVE = 3;
  final public const TYPE_WOW = 4;

  final public const ACTION_ADD = 'add';
  final public const ACTION_REMOVE = 'remove';
  // -> new types go here...

  public static array $VALID_TYPES = [
    self::TYPE_THUMBS_UP,
    self::TYPE_SMILE,
    self::TYPE_LOVE,
    self::TYPE_WOW,
    // -> ... and here ...
  ];

  public static array $TYPE_NAMES = [
    self::TYPE_THUMBS_UP => 'thumbs_up',
    self::TYPE_SMILE => 'smile',
    self::TYPE_LOVE => 'love',
    self::TYPE_WOW => 'wow',
    // -> ... and here
  ];

  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @see{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="guid", nullable=false)
   */
  protected string $project_id;

  /**
   * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="likes", fetch="LAZY")
   *
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
   */
  protected Project $project;

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="guid", nullable=false)
   */
  protected string $user_id;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, inversedBy="likes", fetch="LAZY")
   *
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
   */
  protected User $user;

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="integer", nullable=false, options={"default": 0})
   */
  protected int $type = self::TYPE_THUMBS_UP;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?\DateTime $created_at = null;

  public function __construct(Project $project, User $user, int $type)
  {
    $this->setProject($project);
    $this->setUser($user);
    $this->setType($type);
  }

  public function __toString(): string
  {
    return $this->project.'';
  }

  public static function isValidType(int $type): bool
  {
    return in_array($type, self::$VALID_TYPES, true);
  }

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps(): void
  {
    if (null === $this->getCreatedAt()) {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setProject(Project $project): ProjectLike
  {
    $this->project = $project;
    $this->project_id = $project->getId();

    return $this;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function getProjectId(): string
  {
    return $this->project_id;
  }

  public function setUser(User $user): ProjectLike
  {
    $this->user = $user;
    $this->user_id = $user->getId();

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function getUserId(): string
  {
    return $this->user_id;
  }

  public function setType(int $type): ProjectLike
  {
    $this->type = $type;

    return $this;
  }

  public function getType(): int
  {
    return $this->type;
  }

  public function getTypeAsString(): ?string
  {
    return self::$TYPE_NAMES[$this->type];
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): ProjectLike
  {
    $this->created_at = $created_at;

    return $this;
  }
}
