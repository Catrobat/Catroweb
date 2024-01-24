<?php

namespace App\DB\Entity\Project\Special;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\Project\EventListener\FeaturedProjectImageListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=FeaturedRepository::class)
 *
 * @ORM\EntityListeners({FeaturedProjectImageListener::class})
 *
 * @ORM\Table(name="featured")
 */
class FeaturedProject extends SpecialProject
{
  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $url = null;

  public function setImageType(string $image): FeaturedProject
  {
    $this->imagetype = $image;

    return $this;
  }

  public function setProject(?Project $project): FeaturedProject
  {
    $this->project = $project;

    return $this;
  }

  public function setUrl(?string $url): FeaturedProject
  {
    $this->url = $url;

    return $this;
  }

  public function setActive(bool $active): FeaturedProject
  {
    $this->active = $active;

    return $this;
  }

  public function setNewFeaturedImage(File $file): void
  {
    $this->file = $file;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }
}
