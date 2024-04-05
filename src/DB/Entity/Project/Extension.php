<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\EntityRepository\Project\ExtensionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'extension')]
#[ORM\Index(columns: ['internal_title'], name: 'internal_title_idx')]
#[ORM\Entity(repositoryClass: ExtensionRepository::class)]
class Extension implements \Stringable
{
  /**
   * Static Tags - added/updated with UpdateTagsCommand.
   */
  final public const ARDUINO = 'arduino';
  final public const DRONE = 'drone';
  final public const PHIRO = 'phiro';
  final public const RASPBERRY_PI = 'raspberry_pi';
  final public const EMBROIDERY = 'embroidery';
  final public const MINDSTORMS = 'mindstorms';
  final public const MULTIPLAYER = 'multiplayer';

  #[ORM\Id]
  #[ORM\Column(type: 'integer')]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\ManyToMany(targetEntity: Program::class, mappedBy: 'extensions')]
  protected Collection $programs;

  #[ORM\Column(name: 'internal_title', type: 'string', nullable: false)]
  protected string $internal_title = '';

  #[ORM\Column(name: 'title_ltm_code', type: 'string', nullable: false)]
  protected string $title_ltm_code = '';

  #[ORM\Column(name: 'enabled', type: 'boolean', options: ['default' => true])]
  protected bool $enabled = true;

  public function __construct()
  {
    $this->programs = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->internal_title;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getInternalTitle(): string
  {
    return $this->internal_title;
  }

  public function setInternalTitle(string $internal_title): Extension
  {
    $this->internal_title = $internal_title;

    return $this;
  }

  public function getTitleLtmCode(): string
  {
    return $this->title_ltm_code;
  }

  public function setTitleLtmCode(string $title_ltm_code): Extension
  {
    $this->title_ltm_code = $title_ltm_code;

    return $this;
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): Extension
  {
    $this->enabled = $enabled;

    return $this;
  }

  public function addProgram(Program $program): void
  {
    if ($this->programs->contains($program)) {
      return;
    }
    $this->programs->add($program);
  }

  public function removeProgram(Program $program): void
  {
    $this->programs->removeElement($program);
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }

  public function getProjectCount(): int
  {
    return count($this->programs);
  }

  public function removeAllPrograms(): void
  {
    $this->programs->clear();
  }
}
