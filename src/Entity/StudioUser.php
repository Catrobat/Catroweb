<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * Class StudioUser.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Studios\StudioUserRepository")
 * @ORM\Table(name="studio_user")
 */
class StudioUser
{
  /**
   * adding new constant requires adding it to the enum in the annotation of the column.
   */
  public const ROLE_ADMIN = 'admin';
  public const ROLE_MEMBER = 'member';

  public const STATUS_ACTIVE = 'active';
  public const STATUS_BANNED = 'banned';
  public const STATUS_PENDING_REQUEST = 'pending_request';

  private array $roles = [self::ROLE_ADMIN, self::ROLE_MEMBER];
  private array $statuses = [self::STATUS_ACTIVE, self::STATUS_BANNED, self::STATUS_PENDING_REQUEST];

  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id;

  /**
   * @ORM\ManyToOne(targetEntity="Studio", cascade={"persist"})
   * @ORM\JoinColumn(name="studio", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected Studio $studio;

  /**
   * @ORM\OneToOne(targetEntity="StudioActivity", cascade={"persist"})
   * @ORM\JoinColumn(name="activity", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected StudioActivity $activity;

  /**
   * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected User $user;

  /**
   * @ORM\Column(name="role", type="string", columnDefinition="ENUM('admin', 'member')", nullable=false)
   */
  protected string $role;

  /**
   * @ORM\Column(name="status", type="string", columnDefinition="ENUM('active', 'banned', 'pending_request')", nullable=false)
   */
  protected string $status;

  /**
   * @ORM\Column(name="updated_on", type="datetime", length=300, nullable=true)
   */
  protected ?DateTime $updated_on;

  /**
   * @ORM\Column(name="created_on", type="datetime", length=300, nullable=false)
   */
  protected DateTime $created_on;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): StudioUser
  {
    $this->id = $id;

    return $this;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): StudioUser
  {
    $this->studio = $studio;

    return $this;
  }

  public function getActivity(): StudioActivity
  {
    return $this->activity;
  }

  public function setActivity(StudioActivity $activity): StudioUser
  {
    $this->activity = $activity;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): StudioUser
  {
    $this->user = $user;

    return $this;
  }

  public function getRole(): string
  {
    return $this->role;
  }

  public function setRole(string $role): StudioUser
  {
    if (!in_array($role, $this->roles, true)) {
      throw new InvalidArgumentException('invalid user role given');
    }
    $this->role = $role;

    return $this;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): StudioUser
  {
    if (!in_array($status, $this->statuses, true)) {
      throw new InvalidArgumentException('invalid user status given');
    }
    $this->status = $status;

    return $this;
  }

  public function getUpdatedOn(): ?DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?DateTime $updated_on): StudioUser
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(DateTime $created_on): StudioUser
  {
    $this->created_on = $created_on;

    return $this;
  }
}
