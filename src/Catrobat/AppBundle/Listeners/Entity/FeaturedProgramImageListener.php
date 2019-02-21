<?php

namespace Catrobat\AppBundle\Listeners\Entity;

use Catrobat\AppBundle\Entity\FeaturedProgram;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Catrobat\AppBundle\Services\FeaturedImageRepository;


/**
 * Class FeaturedProgramImageListener
 * @package Catrobat\AppBundle\Listeners\Entity
 */
class FeaturedProgramImageListener
{
  /**
   * @var FeaturedImageRepository
   */
  private $repository;

  /**
   * FeaturedProgramImageListener constructor.
   *
   * @param FeaturedImageRepository $repository
   */
  public function __construct(FeaturedImageRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
  public function prePersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $featured->setImageType($file->guessExtension());
  }

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
  public function postPersist(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType());
  }

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
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

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
  public function postUpdate(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $file = $featured->file;
    if ($file == null)
    {
      return;
    }
    $this->repository->save($file, $featured->getId(), $featured->getImageType());
  }

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
  public function preRemove(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $featured->removed_id = $featured->getId();
  }

  /**
   * @param FeaturedProgram    $featured
   * @param LifecycleEventArgs $event
   */
  public function postRemove(FeaturedProgram $featured, LifecycleEventArgs $event)
  {
    $this->repository->remove($featured->removed_id, $featured->getImageType());
  }
}
