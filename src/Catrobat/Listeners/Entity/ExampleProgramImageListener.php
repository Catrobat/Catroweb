<?php

namespace App\Catrobat\Listeners\Entity;

use App\Catrobat\Services\ImageRepository;
use App\Entity\ExampleProgram;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ExampleProgramImageListener
{
  private ImageRepository $repository;

  public function __construct(ImageRepository $repository)
  {
    $this->repository = $repository;
  }

  public function prePersist(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file)
    {
      return;
    }
    $example->setImageType($file->guessExtension());
  }

  public function postPersist(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file)
    {
      return;
    }
    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preUpdate(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file)
    {
      $example->setImageType($example->old_image_type);

      return;
    }
    $example->setImageType($file->guessExtension());
  }

  public function postUpdate(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $file = $example->file;
    if (null == $file)
    {
      return;
    }
    $this->repository->save($file, $example->getId(), $example->getImageType(), false);
  }

  public function preRemove(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $example->removed_id = $example->getId();
  }

  public function postRemove(ExampleProgram $example, LifecycleEventArgs $event): void
  {
    $this->repository->remove($example->removed_id, $example->getImageType(), false);
  }
}
