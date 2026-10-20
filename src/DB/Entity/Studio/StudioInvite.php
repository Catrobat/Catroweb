<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioInviteRepository;
use App\DB\Generator\MyUuidGenerator;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A code that lets anyone holding it join a (private) studio as member without approval.
 * Revoking an invite deletes the row; members who joined with it stay.
 */
#[ORM\Table(name: 'studio_invites')]
#[ORM\Entity(repositoryClass: StudioInviteRepository::class)]
class StudioInvite
{
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  #[ORM\Column(type: Types::GUID)]
  protected ?string $id = null;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class)]
  protected Studio $studio;

  #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  protected ?User $created_by = null;

  #[ORM\Column(type: Types::STRING, length: 16, unique: true)]
  protected string $code;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  protected int $uses = 0;

  #[ORM\Column(type: Types::INTEGER, nullable: true)]
  protected ?int $max_uses = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $expires_at = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected \DateTime $created_on;

  public function __construct(Studio $studio, string $code, ?User $created_by, ?int $max_uses, ?\DateTime $expires_at)
  {
    $this->studio = $studio;
    $this->code = $code;
    $this->created_by = $created_by;
    $this->max_uses = $max_uses;
    $this->expires_at = $expires_at;
    $this->created_on = new \DateTime();
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function getStudio(): Studio
  {
    return $this->studio;
  }

  public function getCreatedBy(): ?User
  {
    return $this->created_by;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getUses(): int
  {
    return $this->uses;
  }

  public function getMaxUses(): ?int
  {
    return $this->max_uses;
  }

  public function getExpiresAt(): ?\DateTime
  {
    return $this->expires_at;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function isUsable(\DateTimeInterface $now = new \DateTimeImmutable()): bool
  {
    return (null === $this->expires_at || $this->expires_at > $now)
      && (null === $this->max_uses || $this->uses < $this->max_uses);
  }
}
