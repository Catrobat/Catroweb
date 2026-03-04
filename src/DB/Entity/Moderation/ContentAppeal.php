<?php

declare(strict_types=1);

namespace App\DB\Entity\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentAppealRepository;
use App\DB\Enum\AppealState;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'content_appeal')]
#[ORM\UniqueConstraint(name: 'unique_content_appeal', columns: ['content_type', 'content_id', 'appellant_id'])]
#[ORM\Index(name: 'ca_state_created_idx', columns: ['state', 'created_at'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ContentAppealRepository::class)]
class ContentAppeal
{
  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\Column(name: 'content_type', type: Types::STRING, length: 20)]
  private string $content_type;

  #[ORM\Column(name: 'content_id', type: Types::STRING, length: 255)]
  private string $content_id;

  #[ORM\JoinColumn(name: 'appellant_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  private ?User $appellant = null;

  #[ORM\Column(name: 'reason', type: Types::TEXT)]
  private string $reason;

  #[ORM\Column(name: 'state', type: Types::SMALLINT, options: ['default' => 1])]
  private int $state = AppealState::Pending->value;

  #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
  private ?\DateTime $created_at = null;

  #[ORM\Column(name: 'resolved_at', type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?\DateTime $resolved_at = null;

  #[ORM\JoinColumn(name: 'resolved_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  private ?User $resolved_by = null;

  #[ORM\Column(name: 'resolution_note', type: Types::TEXT, nullable: true)]
  private ?string $resolution_note = null;

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (null === $this->created_at) {
      $this->created_at = TimeUtils::getDateTime();
    }
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): ContentAppeal
  {
    $this->id = $id;

    return $this;
  }

  public function getContentType(): string
  {
    return $this->content_type;
  }

  public function setContentType(string $content_type): ContentAppeal
  {
    $this->content_type = $content_type;

    return $this;
  }

  public function getContentId(): string
  {
    return $this->content_id;
  }

  public function setContentId(string $content_id): ContentAppeal
  {
    $this->content_id = $content_id;

    return $this;
  }

  public function getAppellant(): ?User
  {
    return $this->appellant;
  }

  public function setAppellant(?User $appellant): ContentAppeal
  {
    $this->appellant = $appellant;

    return $this;
  }

  public function getReason(): string
  {
    return $this->reason;
  }

  public function setReason(string $reason): ContentAppeal
  {
    $this->reason = $reason;

    return $this;
  }

  public function getState(): int
  {
    return $this->state;
  }

  public function setState(int $state): ContentAppeal
  {
    $this->state = $state;

    return $this;
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): ContentAppeal
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getResolvedAt(): ?\DateTime
  {
    return $this->resolved_at;
  }

  public function setResolvedAt(?\DateTime $resolved_at): ContentAppeal
  {
    $this->resolved_at = $resolved_at;

    return $this;
  }

  public function getResolvedBy(): ?User
  {
    return $this->resolved_by;
  }

  public function setResolvedBy(?User $resolved_by): ContentAppeal
  {
    $this->resolved_by = $resolved_by;

    return $this;
  }

  public function getResolutionNote(): ?string
  {
    return $this->resolution_note;
  }

  public function setResolutionNote(?string $resolution_note): ContentAppeal
  {
    $this->resolution_note = $resolution_note;

    return $this;
  }
}
