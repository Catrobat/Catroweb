<?php

namespace App\Project\EventListener;

use App\DB\Entity\Project\Special\ExampleProject;
use App\Storage\ImageRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ExampleProjectImageListener
{
  public function __construct(private readonly ImageRepository $repository)
  {
  }

  public function prePersist(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }
    $example->setImageType($file->guessExtension());
  }

  public function postPersist(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }
    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preUpdate(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      $example->setImageType($example->old_image_type);

      return;
    }
    $example->setImageType($file->guessExtension());
  }

  public function postUpdate(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file) {
      return;
    }
    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preRemove(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $example->removed_id = $example->getId();
  }

  public function postRemove(ExampleProject $example, LifecycleEventArgs $event): void
  {
    $this->repository->remove($example->removed_id, $example->getImageType(), false);
  }
}
