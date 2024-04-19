<?php

declare(strict_types=1);

namespace App\Project\EventListener;

use App\DB\Entity\Project\Special\FeaturedProgram;
use App\Storage\ImageRepository;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class FeaturedProjectImageListener
{
  public function __construct(private readonly ImageRepository $repository)
  {
  }

  public function prePersist(FeaturedProgram $featured, PrePersistEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file) {
      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postPersist(FeaturedProgram $featured, PostPersistEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file) {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType(), true);
  }

  public function preUpdate(FeaturedProgram $featured, PreUpdateEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file) {
      $featured->setImageType($featured->old_image_type);

      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postUpdate(FeaturedProgram $featured, PostUpdateEventArgs $event): void
  {
    $file = $featured->file;
    if (null == $file) {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType(), true);
  }

  public function preRemove(FeaturedProgram $featured, PreRemoveEventArgs $event): void
  {
    $featured->removed_id = $featured->getId();
  }

  public function postRemove(FeaturedProgram $featured, PostRemoveEventArgs $event): void
  {
    $this->repository->remove($featured->removed_id, $featured->getImageType(), true);
  }
}
