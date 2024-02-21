<?php

namespace App\DB\Entity\Project;

use App\DB\EntityRepository\Project\ExtensionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="extension",
 *     indexes={
 *
 *         @ORM\Index(name="internal_title_idx", columns={"internal_title"}),
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass=ExtensionRepository::class)
 */
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

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="integer")
   *
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToMany(targetEntity=Project::class, mappedBy="extensions")
   */
  protected Collection $projects;

  /**
   * @ORM\Column(name="internal_title", type="string", nullable=false)
   */
  protected string $internal_title = '';

  /**
   * @ORM\Column(name="title_ltm_code", type="string", nullable=false)
   */
  protected string $title_ltm_code = '';

  /**
   * @ORM\Column(name="enabled", type="boolean", options={"default": true})
   */
  protected bool $enabled = true;

  public function __construct()
  {
    $this->projects = new ArrayCollection();
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

  public function addProject(Project $project): void
  {
    if ($this->projects->contains($project)) {
      return;
    }
    $this->projects->add($project);
  }

  public function removeProject(Project $project): void
  {
    $this->projects->removeElement($project);
  }

  public function getProjects(): Collection
  {
    return $this->projects;
  }

  public function getProjectCount(): int
  {
    return count($this->projects);
  }

  public function removeAllProjects(): void
  {
    $this->projects->clear();
  }
}
