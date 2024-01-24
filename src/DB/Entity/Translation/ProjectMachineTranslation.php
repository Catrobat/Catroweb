<?php

namespace App\DB\Entity\Translation;

use App\DB\Entity\Project\Project;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity(repositoryClass="App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository")
 *
 * @ORM\Table(name="project_machine_translation")
 *
 * @HasLifecycleCallbacks
 */
class ProjectMachineTranslation extends MachineTranslation
{
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  private ?string $cached_name = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $cached_description = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $cached_credits = null;

  public function __construct(/**
   * @ORM\ManyToOne(targetEntity=Project::class)
   *
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
   */
    protected Project $project, string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    parent::__construct($source_language, $target_language, $provider, $usage_count);
  }

  public function getProject(): Project
  {
    return $this->project;
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

  public function setCachedTranslation(?string $name, ?string $description, ?string $credits): void
  {
    $this->cached_name = $name;
    $this->cached_description = $description;
    $this->cached_credits = $credits;
  }

  public function invalidateCachedTranslation(): void
  {
    $this->cached_name = null;
    $this->cached_description = null;
    $this->cached_credits = null;
  }
}
