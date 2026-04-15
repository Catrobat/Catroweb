<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\Project\Special\ExampleProject;
use App\Storage\ImageRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ExampleProject::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: ExampleProject::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ExampleProject::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: ExampleProject::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: ExampleProject::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: ExampleProject::class)]
readonly class ExampleProjectImageEntityListeners
{
  public function __construct(private ImageRepository $repository)
  {
  }

  public function prePersist(ExampleProject $example_project, PrePersistEventArgs $args): void
  {
    $file = $example_project->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $example_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postPersist(ExampleProject $example_project, PostPersistEventArgs $args): void
  {
    $file = $example_project->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $this->repository->save($file, $example_project->getId(), $example_project->getImageType(), false);
  }

  public function preUpdate(ExampleProject $example_project, PreUpdateEventArgs $args): void
  {
    $file = $example_project->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      $example_project->setImageType($example_project->old_image_type);

      return;
    }

    $example_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postUpdate(ExampleProject $example_project, PostUpdateEventArgs $args): void
  {
    $file = $example_project->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $this->repository->save($file, $example_project->getId(), $example_project->getImageType(), false);
  }

  public function preRemove(ExampleProject $example_project, PreRemoveEventArgs $args): void
  {
    $example_project->removed_id = $example_project->getId();
  }

  public function postRemove(ExampleProject $example_project, PostRemoveEventArgs $args): void
  {
    $this->repository->remove($example_project->removed_id, $example_project->getImageType(), false);
  }
}
