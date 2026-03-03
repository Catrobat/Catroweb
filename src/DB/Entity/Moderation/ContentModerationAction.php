<?php

declare(strict_types=1);

namespace App\DB\Entity\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentModerationActionRepository;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'content_moderation_action')]
#[ORM\Index(name: 'cma_content_idx', columns: ['content_type', 'content_id'])]
#[ORM\Index(name: 'cma_created_idx', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ContentModerationActionRepository::class)]
class ContentModerationAction
{
  final public const string ACTION_AUTO_HIDDEN = 'auto_hidden';
  final public const string ACTION_ADMIN_CONFIRMED = 'admin_confirmed';
  final public const string ACTION_ADMIN_REVERSED = 'admin_reversed';
  final public const string ACTION_APPEALED = 'appealed';
  final public const string ACTION_APPEAL_APPROVED = 'appeal_approved';
  final public const string ACTION_APPEAL_REJECTED = 'appeal_rejected';
  final public const string ACTION_BRIGADING_SUSPECTED = 'brigading_suspected';

  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\Column(name: 'content_type', type: Types::STRING, length: 20)]
  private string $content_type;

  #[ORM\Column(name: 'content_id', type: Types::STRING, length: 255)]
  private string $content_id;

  #[ORM\Column(name: 'action', type: Types::STRING, length: 30)]
  private string $action;

  #[ORM\Column(name: 'cumulative_score', type: Types::FLOAT, nullable: true)]
  private ?float $cumulative_score = null;

  #[ORM\JoinColumn(name: 'performed_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  private ?User $performed_by = null;

  #[ORM\Column(name: 'note', type: Types::TEXT, nullable: true)]
  private ?string $note = null;

  #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
  private ?\DateTime $created_at = null;

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

  public function setId(?int $id): ContentModerationAction
  {
    $this->id = $id;

    return $this;
  }

  public function getContentType(): string
  {
    return $this->content_type;
  }

  public function setContentType(string $content_type): ContentModerationAction
  {
    $this->content_type = $content_type;

    return $this;
  }

  public function getContentId(): string
  {
    return $this->content_id;
  }

  public function setContentId(string $content_id): ContentModerationAction
  {
    $this->content_id = $content_id;

    return $this;
  }

  public function getAction(): string
  {
    return $this->action;
  }

  public function setAction(string $action): ContentModerationAction
  {
    $this->action = $action;

    return $this;
  }

  public function getCumulativeScore(): ?float
  {
    return $this->cumulative_score;
  }

  public function setCumulativeScore(?float $cumulative_score): ContentModerationAction
  {
    $this->cumulative_score = $cumulative_score;

    return $this;
  }

  public function getPerformedBy(): ?User
  {
    return $this->performed_by;
  }

  public function setPerformedBy(?User $performed_by): ContentModerationAction
  {
    $this->performed_by = $performed_by;

    return $this;
  }

  public function getNote(): ?string
  {
    return $this->note;
  }

  public function setNote(?string $note): ContentModerationAction
  {
    $this->note = $note;

    return $this;
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): ContentModerationAction
  {
    $this->created_at = $created_at;

    return $this;
  }
}
