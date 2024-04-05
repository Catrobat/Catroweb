<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio_user')]
#[ORM\Entity(repositoryClass: StudioUserRepository::class)]
class StudioUser
{
  /**
   * adding new constant requires adding it to the enum in the annotation of the column.
   */
  final public const ROLE_ADMIN = 'admin';
  final public const ROLE_MEMBER = 'member';

  final public const STATUS_ACTIVE = 'active';
  final public const STATUS_BANNED = 'banned';
  final public const STATUS_PENDING_REQUEST = 'pending_request';

  private array $roles = [self::ROLE_ADMIN, self::ROLE_MEMBER];
  private array $statuses = [self::STATUS_ACTIVE, self::STATUS_BANNED, self::STATUS_PENDING_REQUEST];

  #[ORM\Id]
  #[ORM\Column(name: 'id', type: 'integer')]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class, cascade: ['persist'])]
  protected Studio $studio;

  #[ORM\JoinColumn(name: 'activity', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\OneToOne(targetEntity: StudioActivity::class, cascade: ['persist'])]
  protected StudioActivity $activity;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
  protected User $user;

  #[ORM\Column(name: 'role', type: 'string', nullable: false, columnDefinition: "ENUM('admin', 'member')")]
  protected string $role;

  #[ORM\Column(name: 'status', type: 'string', nullable: false, columnDefinition: "ENUM('active', 'banned', 'pending_request')")]
  protected string $status;

  #[ORM\Column(name: 'updated_on', type: 'datetime', length: 300, nullable: true)]
  protected ?\DateTime $updated_on = null;

  #[ORM\Column(name: 'created_on', type: 'datetime', length: 300, nullable: false)]
  protected \DateTime $created_on;

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
      throw new \InvalidArgumentException('invalid user role given');
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
      throw new \InvalidArgumentException('invalid user status given');
    }
    $this->status = $status;

    return $this;
  }

  public function getUpdatedOn(): ?\DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?\DateTime $updated_on): StudioUser
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(\DateTime $created_on): StudioUser
  {
    $this->created_on = $created_on;

    return $this;
  }

  public function isAdmin(): bool
  {
    return StudioUser::ROLE_ADMIN === $this->getRole();
  }

  public function isMember(): bool
  {
    return StudioUser::ROLE_MEMBER === $this->getRole();
  }
}
