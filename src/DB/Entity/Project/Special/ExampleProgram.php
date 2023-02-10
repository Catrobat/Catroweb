<?php

namespace App\DB\Entity\Project\Special;

use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Project\Special\ExampleRepository;
use App\Project\EventListener\ExampleProgramImageListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ExampleRepository::class)
 *
 * @ORM\EntityListeners({ExampleProgramImageListener::class})
 *
 * @ORM\Table(name="example")
 */
class ExampleProgram extends SpecialProgram
{
  public function setImageType(string $image): ExampleProgram
  {
    $this->imagetype = $image;

    return $this;
  }

  public function setProgram(?Program $program): ExampleProgram
  {
    $this->program = $program;

    return $this;
  }

  public function setActive(bool $active): ExampleProgram
  {
    $this->active = $active;

    return $this;
  }

  public function setNewExampleImage(File $file): void
  {
    $this->file = $file;
  }
}
