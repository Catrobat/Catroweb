<?php

declare(strict_types=1);

namespace App\Project\EventListener;

use App\DB\Entity\Project\Special\ExampleProgram;
use App\Storage\ImageRepository;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ExampleProjectImageListener
{
  public function __construct(private readonly ImageRepository $repository)
  {
  }

  public function prePersist(ExampleProgram $example, PrePersistEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }

    $example->setImageType($file->guessExtension());
  }

  public function postPersist(ExampleProgram $example, PostPersistEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }

    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preUpdate(ExampleProgram $example, PreUpdateEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      $example->setImageType($example->old_image_type);

      return;
    }

    $example->setImageType($file->guessExtension());
  }

  public function postUpdate(ExampleProgram $example, PostUpdateEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }

    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preRemove(ExampleProgram $example, PreRemoveEventArgs $event): void
  {
    $example->removed_id = $example->getId();
  }

  public function postRemove(ExampleProgram $example, PostRemoveEventArgs $event): void
  {
    $this->repository->remove($example->removed_id, $example->getImageType(), false);
  }
}
