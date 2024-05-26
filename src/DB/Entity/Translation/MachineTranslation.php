<?php

declare(strict_types=1);

namespace App\DB\Entity\Translation;

use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

abstract class MachineTranslation
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  protected ?int $id = null;

  #[ORM\Column(type: Types::FLOAT)]
  protected float $usage_per_month;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected \DateTime $last_modified_at;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected \DateTime $created_at;

  public function __construct(
    #[ORM\Column(type: Types::STRING, length: 5)] protected string $source_language,
    #[ORM\Column(type: Types::STRING, length: 5)] protected string $target_language,
    #[ORM\Column(type: Types::STRING)] protected string $provider,
    #[ORM\Column(type: Types::INTEGER)] protected int $usage_count = 1)
  {
    $this->usage_per_month = $usage_count;
  }

  public function incrementCount(): void
  {
    ++$this->usage_count;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getSourceLanguage(): string
  {
    return $this->source_language;
  }

  public function getTargetLanguage(): string
  {
    return $this->target_language;
  }

  public function getProvider(): string
  {
    return $this->provider;
  }

  public function getUsageCount(): int
  {
    return $this->usage_count;
  }

  public function getUsagePerMonth(): float
  {
    return $this->usage_per_month;
  }

  public function getLastModifiedAt(): \DateTime
  {
    return $this->last_modified_at;
  }

  public function getCreatedAt(): \DateTime
  {
    return $this->created_at;
  }

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function initTimestamps(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();
    $this->created_at = $this->last_modified_at;
  }

  /**
   * @throws \Exception
   */
  #[ORM\PreUpdate]
  public function preUpdate(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();

    $date_interval = $this->last_modified_at->diff($this->created_at);
    $months = $date_interval->days / 30;

    $this->usage_per_month = 1 > $months ? $this->usage_count : $this->usage_count / $months;
  }
}
