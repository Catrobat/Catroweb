<?php

declare(strict_types=1);

namespace App\DB\Entity\Moderation;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\ReportState;
use App\DB\Generator\MyUuidGenerator;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'content_report')]
#[ORM\UniqueConstraint(name: 'unique_user_report', columns: ['reporter_id', 'content_type', 'content_id'])]
#[ORM\Index(name: 'cr_content_idx', columns: ['content_type', 'content_id', 'state'])]
#[ORM\Index(name: 'cr_reporter_idx', columns: ['reporter_id', 'state'])]
#[ORM\Index(name: 'cr_state_created_idx', columns: ['state', 'created_at'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ContentReportRepository::class)]
class ContentReport
{
  #[ORM\Column(name: 'id', type: Types::GUID)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  private ?string $id = null;

  #[ORM\JoinColumn(name: 'reporter_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  private ?User $reporter = null;

  #[ORM\Column(name: 'content_type', type: Types::STRING, length: 20)]
  private string $content_type;

  #[ORM\Column(name: 'content_id', type: Types::STRING, length: 255)]
  private string $content_id;

  #[ORM\Column(name: 'category', type: Types::STRING, length: 100)]
  private string $category;

  #[ORM\Column(name: 'note', type: Types::TEXT, nullable: true)]
  private ?string $note = null;

  #[ORM\Column(name: 'state', type: Types::SMALLINT, options: ['default' => 1])]
  private int $state = ReportState::New->value;

  #[ORM\Column(name: 'reporter_trust_score', type: Types::FLOAT)]
  private float $reporter_trust_score = 0.0;

  #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
  private ?\DateTime $created_at = null;

  #[ORM\Column(name: 'resolved_at', type: Types::DATETIME_MUTABLE, nullable: true)]
  private ?\DateTime $resolved_at = null;

  #[ORM\JoinColumn(name: 'resolved_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  private ?User $resolved_by = null;

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (!$this->created_at instanceof \DateTime) {
      $this->created_at = TimeUtils::getDateTime();
    }
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function setId(?string $id): ContentReport
  {
    $this->id = $id;

    return $this;
  }

  public function getReporter(): ?User
  {
    return $this->reporter;
  }

  public function setReporter(?User $reporter): ContentReport
  {
    $this->reporter = $reporter;

    return $this;
  }

  public function getContentType(): string
  {
    return $this->content_type;
  }

  public function setContentType(string $content_type): ContentReport
  {
    $this->content_type = $content_type;

    return $this;
  }

  public function getContentId(): string
  {
    return $this->content_id;
  }

  public function setContentId(string $content_id): ContentReport
  {
    $this->content_id = $content_id;

    return $this;
  }

  public function getCategory(): string
  {
    return $this->category;
  }

  public function setCategory(string $category): ContentReport
  {
    $this->category = $category;

    return $this;
  }

  public function getNote(): ?string
  {
    return $this->note;
  }

  public function setNote(?string $note): ContentReport
  {
    $this->note = $note;

    return $this;
  }

  public function getState(): int
  {
    return $this->state;
  }

  public function setState(int $state): ContentReport
  {
    $this->state = $state;

    return $this;
  }

  public function getReporterTrustScore(): float
  {
    return $this->reporter_trust_score;
  }

  public function setReporterTrustScore(float $reporter_trust_score): ContentReport
  {
    $this->reporter_trust_score = $reporter_trust_score;

    return $this;
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): ContentReport
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getResolvedAt(): ?\DateTime
  {
    return $this->resolved_at;
  }

  public function setResolvedAt(?\DateTime $resolved_at): ContentReport
  {
    $this->resolved_at = $resolved_at;

    return $this;
  }

  public function getResolvedBy(): ?User
  {
    return $this->resolved_by;
  }

  public function setResolvedBy(?User $resolved_by): ContentReport
  {
    $this->resolved_by = $resolved_by;

    return $this;
  }
}
