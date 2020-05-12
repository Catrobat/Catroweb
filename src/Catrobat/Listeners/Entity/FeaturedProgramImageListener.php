<?php

namespace App\Catrobat\Listeners\Entity;

use App\Catrobat\Services\ImageRepository;
use App\Entity\FeaturedProgram;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FeaturedProgramImageListener
{
  private ImageRepository $repository;

  public function __construct(ImageRepository $repository)
  {
    $this->repository = $repository;
  }

  public function prePersist(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file)
    {
      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postPersist(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType(), true);
  }

  public function preUpdate(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file)
    {
      $featured->setImageType($featured->old_image_type);

      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postUpdate(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType(), true);
  }

  public function preRemove(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $featured->removed_id = $featured->getId();
  }

  public function postRemove(FeaturedProgram $featured, LifecycleEventArgs $event): void
  {
    $this->repository->remove($featured->removed_id, $featured->getImageType(), true);
  }
}
