<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeaturedRepository")
 * @ORM\EntityListeners({"App\Catrobat\Listeners\Entity\FeaturedProgramImageListener"})
 * @ORM\Table(name="featured")
 */
class FeaturedProgram
{
  public ?File $file = null;

  public ?int $removed_id = null;

  public ?string $old_image_type = null;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string")
   */
  protected string $imagetype;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $url = null;

  /**
   * @ORM\Column(type="boolean")
   */
  protected bool $active = true;

  /**
   * @ORM\ManyToOne(targetEntity="Flavor", fetch="EAGER")
   */
  protected ?Flavor $flavor = null;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $priority = 0;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $for_ios = false;

  /**
   * @ORM\ManyToOne(targetEntity="Program", fetch="EAGER")
   */
  private ?Program $program = null;

  public function getFlavor(): ?Flavor
  {
    return $this->flavor;
  }

  public function setFlavor(Flavor $flavor): void
  {
    $this->flavor = $flavor;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setImageType(string $image): FeaturedProgram
  {
    $this->imagetype = $image;

    return $this;
  }

  public function getImageType(): string
  {
    return $this->imagetype;
  }

  public function setProgram(?Program $program): FeaturedProgram
  {
    $this->program = $program;

    return $this;
  }

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function setUrl(?string $url): FeaturedProgram
  {
    $this->url = $url;

    return $this;
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): FeaturedProgram
  {
    $this->active = $active;

    return $this;
  }

  public function setNewFeaturedImage(File $file): void
  {
    $this->file = $file;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): void
  {
    $this->priority = $priority;
  }

  public function getForIos(): bool
  {
    return $this->for_ios;
  }

  public function setForIos(bool $for_ios): void
  {
    $this->for_ios = $for_ios;
  }
}
