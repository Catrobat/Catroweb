<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\Project\Special\ExampleProgram;
use App\Storage\ImageRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ExampleProgram::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ExampleProgram::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ExampleProgram::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ExampleProgram::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: ExampleProgram::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: ExampleProgram::class)]
readonly class ExampleProjectImageEntityListeners
{
  public function __construct(private ImageRepository $repository)
  {
  }

  public function prePersist(ExampleProgram $example_project, PrePersistEventArgs $args): void
  {
    $file = $example_project->file;
    if (null === $file) {
      return;
    }

    $example_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postPersist(ExampleProgram $example_project, PostPersistEventArgs $args): void
  {
    $file = $example_project->file;
    if (null === $file) {
      return;
    }

    $this->repository->save($file, $example_project->getId(), $example_project->getImageType(), false);
  }

  public function preUpdate(ExampleProgram $example_project, PreUpdateEventArgs $args): void
  {
    $file = $example_project->file;
    if (null === $file) {
      $example_project->setImageType($example_project->old_image_type);

      return;
    }

    $example_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postUpdate(ExampleProgram $example_project, PostUpdateEventArgs $args): void
  {
    $file = $example_project->file;
    if (null === $file) {
      return;
    }

    $this->repository->save($file, $example_project->getId(), $example_project->getImageType(), false);
  }

  public function preRemove(ExampleProgram $example_project, PreRemoveEventArgs $args): void
  {
    $example_project->removed_id = $example_project->getId();
  }

  public function postRemove(ExampleProgram $example_project, PostRemoveEventArgs $args): void
  {
    $this->repository->remove($example_project->removed_id, $example_project->getImageType(), false);
  }
}
