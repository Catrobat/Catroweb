<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="extension")
 * @ORM\Entity(repositoryClass="App\Repository\ExtensionRepository")
 */
class Extension
{
  /**
   * Static Tags - added/updated with UpdateTagsCommand.
   */
  public const ARDUINO = 'arduino';
  public const DRONE = 'drone';
  public const PHIRO = 'phiro';
  public const RASPBERRY_PI = 'raspberry_pi';
  public const EMBROIDERY = 'embroidery';
  public const MINDSTORMS = 'mindstorms';

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Program", mappedBy="extensions")
   */
  protected Collection $programs;

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
    $this->programs = new ArrayCollection();
  }

  public function __toString()
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
