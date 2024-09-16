<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\Project\Special\FeaturedProgram;
use App\Storage\ImageRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: FeaturedProgram::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: FeaturedProgram::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: FeaturedProgram::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: FeaturedProgram::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: FeaturedProgram::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: FeaturedProgram::class)]
readonly class FeaturedProjectImageEntityListeners
{
  public function __construct(private ImageRepository $repository)
  {
  }

  public function prePersist(FeaturedProgram $featured_project, PrePersistEventArgs $args): void
  {
    $file = $featured_project->file;
    if (null === $file) {
      return;
    }

    $featured_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postPersist(FeaturedProgram $featured_project, PostPersistEventArgs $args): void
  {
    $file = $featured_project->file;
    if (null === $file) {
      return;
    }

    $this->repository->save($file, $featured_project->getId(), $featured_project->getImageType(), true);
  }

  public function preUpdate(FeaturedProgram $featured_project, PreUpdateEventArgs $args): void
  {
    $file = $featured_project->file;
    if (null === $file) {
      $featured_project->setImageType($featured_project->old_image_type);

      return;
    }

    $featured_project->setImageType($file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postUpdate(FeaturedProgram $featured_project, PostUpdateEventArgs $args): void
  {
    $file = $featured_project->file;
    if (null === $file) {
      return;
    }

    $this->repository->save($file, $featured_project->getId(), $featured_project->getImageType(), true);
  }

  public function preRemove(FeaturedProgram $featured_project, PreRemoveEventArgs $args): void
  {
    $featured_project->removed_id = $featured_project->getId();
  }

  public function postRemove(FeaturedProgram $featured_project, PostRemoveEventArgs $args): void
  {
    $this->repository->remove($featured_project->removed_id, $featured_project->getImageType(), true);
  }
}
