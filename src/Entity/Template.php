<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="template")
 * @ORM\Entity(repositoryClass="App\Repository\TemplateRepository")
 */
class Template
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=300)
   */
  protected ?string $name = null;

  /**
   * @ORM\Column(type="boolean")
   */
  protected bool $active = true;

  protected ?File $thumbnail = null;

  protected ?File $landscape_program_file = null;

  protected ?File $portrait_program_file = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): void
  {
    $this->active = $active;
  }

  public function getThumbnail(): ?File
  {
    return $this->thumbnail;
  }

  public function setThumbnail(File $thumbnail): void
  {
    $this->thumbnail = $thumbnail;
  }

  public function getLandscapeProgramFile(): ?File
  {
    return $this->landscape_program_file;
  }

  public function setLandscapeProgramFile(File $landscape_program_file): void
  {
    $this->landscape_program_file = $landscape_program_file;
  }

  public function getPortraitProgramFile(): ?File
  {
    return $this->portrait_program_file;
  }

  public function setPortraitProgramFile(File $portrait_program_file): void
  {
    $this->portrait_program_file = $portrait_program_file;
  }
}
