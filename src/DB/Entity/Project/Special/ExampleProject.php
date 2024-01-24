<?php

namespace App\DB\Entity\Project\Special;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\Special\ExampleRepository;
use App\Project\EventListener\ExampleProjectImageListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ExampleRepository::class)
 *
 * @ORM\EntityListeners({ExampleProjectImageListener::class})
 *
 * @ORM\Table(name="example")
 */
class ExampleProject extends SpecialProject
{
  public function setImageType(string $image): ExampleProject
  {
    $this->imagetype = $image;

    return $this;
  }

  public function setProject(?Project $project): ExampleProject
  {
    $this->project = $project;

    return $this;
  }

  public function setActive(bool $active): ExampleProject
  {
    $this->active = $active;

    return $this;
  }

  public function setNewExampleImage(File $file): void
  {
    $this->file = $file;
  }
}
