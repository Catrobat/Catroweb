<?php

namespace App\Entity\Translation;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

abstract class MachineTranslation
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=5)
   */
  protected string $source_language;

  /**
   * @ORM\Column(type="string", length=5)
   */
  protected string $target_language;

  /**
   * @ORM\Column(type="string")
   */
  protected string $provider;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $usage_count;

  /**
   * @ORM\Column(type="float")
   */
  protected float $usage_per_month;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $last_modified_at;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $created_at;

  public function __construct(string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    $this->source_language = $source_language;
    $this->target_language = $target_language;
    $this->provider = $provider;
    $this->usage_count = $usage_count;
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

  public function getLastModifiedAt(): DateTime
  {
    return $this->last_modified_at;
  }

  public function getCreatedAt(): DateTime
  {
    return $this->created_at;
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function initTimestamps(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();
    $this->created_at = $this->last_modified_at;
  }

  /**
   * @ORM\PreUpdate
   *
   * @throws Exception
   */
  public function preUpdate(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();

    $date_interval = $this->last_modified_at->diff($this->created_at);
    $months = $date_interval->days / 30;

    if (1 > $months) {
      $this->usage_per_month = $this->usage_count;
    } else {
      $this->usage_per_month = $this->usage_count / $months;
    }
  }
}
