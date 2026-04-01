<?php

declare(strict_types=1);

namespace App\DB\Entity\User;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'consent_log')]
#[ORM\Index(columns: ['user_id'], name: 'idx_consent_log_user')]
#[ORM\Index(columns: ['parent_email'], name: 'idx_consent_log_parent_email')]
class ConsentLog
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  protected ?int $id = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  private User $user;

  #[ORM\Column(type: Types::STRING, length: 30)]
  private string $action;

  #[ORM\Column(type: Types::STRING, length: 255)]
  private string $parent_email;

  #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
  private ?string $ip_address = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private \DateTimeInterface $created_at;

  public function __construct(User $user, string $action, string $parent_email, ?string $ip_address = null)
  {
    $this->user = $user;
    $this->action = $action;
    $this->parent_email = $parent_email;
    $this->ip_address = $ip_address;
    $this->created_at = new \DateTime();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function getAction(): string
  {
    return $this->action;
  }

  public function getParentEmail(): string
  {
    return $this->parent_email;
  }

  public function getIpAddress(): ?string
  {
    return $this->ip_address;
  }

  public function getCreatedAt(): \DateTimeInterface
  {
    return $this->created_at;
  }
}
