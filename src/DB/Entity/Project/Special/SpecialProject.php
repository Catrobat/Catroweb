<?php

namespace App\DB\Entity\Project\Special;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

class SpecialProject
{
  public ?File $file = null;

  public ?int $removed_id = null;

  public ?string $old_image_type = null;

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="integer")
   *
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string")
   */
  protected string $imagetype;

  /**
   * @ORM\Column(type="boolean")
   */
  protected bool $active = true;

  /**
   * @ORM\ManyToOne(targetEntity=Flavor::class, fetch="EAGER")
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
   * @ORM\ManyToOne(targetEntity="App\DB\Entity\Project\Project", fetch="EAGER")
   */
  protected ?Project $project = null;

  public function getFlavor(): ?Flavor
  {
    return $this->flavor;
  }

  public function setFlavor(Flavor $flavor): void
  {
    $this->flavor = $flavor;
  }

  public function getImageType(): string
  {
    return $this->imagetype;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProject(): ?Project
  {
    return $this->project;
  }

  public function getActive(): bool
  {
    return $this->active;
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

  public function isExample(): bool
  {
    return true;
  }

  public function getName(): string
  {
    return $this->project->getName();
  }

  public function getUser(): ?User
  {
    return $this->getProject()->getUser();
  }
}
