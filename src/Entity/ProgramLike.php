<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program_like")
 * @ORM\Entity(repositoryClass="App\Repository\ProgramLikeRepository")
 */
class ProgramLike
{
  const TYPE_NONE = 0;
  const TYPE_THUMBS_UP = 1;
  const TYPE_SMILE = 2;
  const TYPE_LOVE = 3;
  const TYPE_WOW = 4;

  const ACTION_ADD = 'add';
  const ACTION_REMOVE = 'remove';
  // -> new types go here...

  public static $VALID_TYPES = [
    self::TYPE_THUMBS_UP,
    self::TYPE_SMILE,
    self::TYPE_LOVE,
    self::TYPE_WOW,
    // -> ... and here ...
  ];

  public static $TYPE_NAMES = [
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
   * @link{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="guid", nullable=false)
   */
  protected $program_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="likes", fetch="LAZY")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   *
   * @var Program
   */
  protected $program;

  /**
   * @ORM\Id
   * @ORM\Column(type="guid", nullable=false)
   */
  protected $user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="likes", fetch="LAZY")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
   *
   * @var User
   */
  protected $user;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false, options={"default": 0})
   */
  protected $type = self::TYPE_THUMBS_UP;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $created_at;

  /**
   * @param int $type
   */
  public function __construct(Program $program, User $user, $type)
  {
    $this->setProgram($program);
    $this->setUser($user);
    $this->setType($type);
    $this->created_at = null;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->program.'';
  }

  /**
   * @param $type
   *
   * @return bool
   */
  public static function isValidType($type)
  {
    return in_array($type, self::$VALID_TYPES, true);
  }

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps()
  {
    if (null == $this->getCreatedAt())
    {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  /**
   * @param \App\Entity\Program $program
   *
   * @return ProgramLike
   */
  public function setProgram(Program $program)
  {
    $this->program = $program;
    $this->program_id = $program->getId();

    return $this;
  }

  /**
   * @return Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * @return int
   */
  public function getProgramId()
  {
    return $this->program_id;
  }

  /**
   * @param \App\Entity\User $user
   *
   * @return ProgramLike
   */
  public function setUser(User $user)
  {
    $this->user = $user;
    $this->user_id = $user->getId();

    return $this;
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @return int
   */
  public function getUserId()
  {
    return $this->user_id;
  }

  /**
   * @param int $type
   *
   * @return ProgramLike
   */
  public function setType($type)
  {
    $this->type = (int) $type;

    return $this;
  }

  /**
   * @return int
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return string|null
   */
  public function getTypeAsString()
  {
    try
    {
      return self::$TYPE_NAMES[$this->type];
    }
    catch (\ErrorException $exception)
    {
      return null;
    }
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->created_at;
  }

  /**
   * @return $this
   */
  public function setCreatedAt(\DateTime $created_at)
  {
    $this->created_at = $created_at;

    return $this;
  }
}
