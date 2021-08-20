<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tags")
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
  /**
   * Static Tags - added/updated with UpdateTagsCommand.
   */
  public const GAME = 'game';
  public const ANIMATION = 'animation';
  public const STORY = 'story';
  public const MUSIC = 'music';
  public const ART = 'art';
  public const EXPERIMENTAL = 'experimental';
  public const TUTORIAL = 'tutorial';

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Program", mappedBy="tags")
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

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getInternalTitle(): string
  {
    return $this->internal_title;
  }

  public function setInternalTitle(string $internal_title): Tag
  {
    $this->internal_title = $internal_title;

    return $this;
  }

  public function getTitleLtmCode(): string
  {
    return $this->title_ltm_code;
  }

  public function setTitleLtmCode(string $title_ltm_code): Tag
  {
    $this->title_ltm_code = $title_ltm_code;

    return $this;
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): Tag
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

  public function getProjectCount(): int
  {
    return count($this->programs);
  }

  public function removeProgram(Program $program): void
  {
    $this->programs->removeElement($program);
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }
}
