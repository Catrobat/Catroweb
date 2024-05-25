<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio_activity')]
#[ORM\Entity(repositoryClass: StudioActivityRepository::class)]
class StudioActivity
{
  /**
   * adding new constant requires adding it to the enum in the annotation of the column.
   */
  final public const string TYPE_COMMENT = 'comment';

  final public const string TYPE_PROJECT = 'project';

  final public const string TYPE_USER = 'user';

  private array $activity_types = [self::TYPE_COMMENT, self::TYPE_PROJECT, self::TYPE_USER];

  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class, cascade: ['persist'])]
  protected Studio $studio;

  #[ORM\Column(name: 'type', type: Types::STRING, nullable: false, columnDefinition: "ENUM('comment', 'project', 'user')")]
  protected string $type;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
  protected User $user;

  #[ORM\Column(name: 'created_on', type: Types::DATETIME_MUTABLE, nullable: false)]
  protected \DateTime $created_on;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): StudioActivity
  {
    $this->id = $id;

    return $this;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): StudioActivity
  {
    $this->studio = $studio;

    return $this;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): StudioActivity
  {
    if (!in_array($type, $this->activity_types, true)) {
      throw new \InvalidArgumentException('invalid activity type given');
    }

    $this->type = $type;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): StudioActivity
  {
    $this->user = $user;

    return $this;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(\DateTime $created_on): StudioActivity
  {
    $this->created_on = $created_on;

    return $this;
  }
}
