<?php

declare(strict_types=1);

namespace App\DB\Entity\Translation;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cached machine translation of a project's name, description and credits.
 */
#[ORM\Table(name: 'project_machine_translation')]
#[ORM\UniqueConstraint(name: 'uniq_project_machine_translation_languages', columns: ['project_id', 'source_language', 'target_language'])]
#[ORM\Entity(repositoryClass: ProjectMachineTranslationRepository::class)]
class ProjectMachineTranslation
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  protected ?int $id = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  private ?string $cached_name = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $cached_description = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $cached_credits = null;

  // md5 of the name, description and credits that were translated: an edit during a translation makes it stale.
  #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
  private ?string $source_hash = null;

  // Detected by LibreTranslate, not picked by a user: only these answer requests without a source language.
  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
  private bool $auto_detected = true;

  public function __construct(
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    protected Project $project,
    #[ORM\Column(type: Types::STRING, length: 5)]
    protected string $source_language,
    #[ORM\Column(type: Types::STRING, length: 5)]
    protected string $target_language,
  ) {
  }

  public static function sourceHash(Project $project): string
  {
    return md5(json_encode([$project->getName(), $project->getDescription(), $project->getCredits()], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE));
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function getSourceLanguage(): string
  {
    return $this->source_language;
  }

  public function getTargetLanguage(): string
  {
    return $this->target_language;
  }

  public function getCachedName(): ?string
  {
    return $this->cached_name;
  }

  public function getCachedDescription(): ?string
  {
    return $this->cached_description;
  }

  public function getCachedCredits(): ?string
  {
    return $this->cached_credits;
  }

  /**
   * Holds a detected-source translation of the project's current texts.
   */
  public function isCurrentAutoDetected(): bool
  {
    return $this->auto_detected && null !== $this->cached_name && self::sourceHash($this->project) === $this->source_hash;
  }

  public function setCachedTranslation(?string $name, ?string $description, ?string $credits, ?string $source_hash = null, bool $auto_detected = true): void
  {
    $this->cached_name = $name;
    $this->cached_description = $description;
    $this->cached_credits = $credits;
    $this->source_hash = $source_hash;
    $this->auto_detected = $auto_detected;
  }

  public function invalidateCachedTranslation(): void
  {
    $this->setCachedTranslation(null, null, null, null, $this->auto_detected);
  }
}
