<?php

namespace App\Catrobat\Listeners\Entity;

use App\Catrobat\Services\FeaturedImageRepository;
use App\Entity\FeaturedProgram;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class FeaturedProgramImageListener.
 */
class FeaturedProgramImageListener
{
  /**
   * @var FeaturedImageRepository
   */
  private $repository;

  /**
   * FeaturedProgramImageListener constructor.
   */
  public function __construct(FeaturedImageRepository $repository)
  {
    $this->repository = $repository;
  }

  public function prePersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if (null == $file)
    {
      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postPersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if (null == $file)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType());
  }

  public function preUpdate(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if (null == $file)
    {
      $featured->setImageType($featured->old_image_type);

      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  public function postUpdate(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if (null == $file)
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
