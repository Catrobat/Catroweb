<?php

namespace Catrobat\AppBundle\Listeners\Entity;

use Catrobat\AppBundle\Entity\FeaturedProgram;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PreUpdate;

class FeaturedProgramImageListener
{
  private $repository;

  public function __construct(FeaturedImageRepository $repository)
  {
    $this->repository = $repository;
  }

  public function prePersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postPersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType());
  }

  public function preUpdate(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      $featured->setImageType($featured->old_image_type);

      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postUpdate(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType());
  }

  public function preRemove(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $featured->removed_id = $featured->getId();
  }

  public function postRemove(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $this->repository->remove($featured->removed_id, $featured->getImageType());
  }
}
