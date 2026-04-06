<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\FeaturedBanner;
use App\Storage\ImageRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: FeaturedBanner::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: FeaturedBanner::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: FeaturedBanner::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: FeaturedBanner::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: FeaturedBanner::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: FeaturedBanner::class)]
readonly class FeaturedBannerImageEntityListeners
{
  public function __construct(private ImageRepository $repository)
  {
  }

  public function prePersist(FeaturedBanner $banner, PrePersistEventArgs $args): void
  {
    $file = $banner->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $banner->setImageType($file->guessExtension() ?? '');
  }

  /**
   * @throws \ImagickException
   */
  public function postPersist(FeaturedBanner $banner, PostPersistEventArgs $args): void
  {
    $file = $banner->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $id = $banner->getId();
    if (null === $id) {
      return;
    }

    $this->repository->save($file, $id, $banner->getImageType(), true);
  }

  public function preUpdate(FeaturedBanner $banner, PreUpdateEventArgs $args): void
  {
    $file = $banner->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      $banner->setImageType($banner->old_image_type ?? '');

      return;
    }

    $banner->setImageType($file->guessExtension() ?? '');
  }

  /**
   * @throws \ImagickException
   */
  public function postUpdate(FeaturedBanner $banner, PostUpdateEventArgs $args): void
  {
    $file = $banner->file;
    if (!$file instanceof \Symfony\Component\HttpFoundation\File\File) {
      return;
    }

    $id = $banner->getId();
    if (null === $id) {
      return;
    }

    $this->repository->save($file, $id, $banner->getImageType(), true);
  }

  public function preRemove(FeaturedBanner $banner, PreRemoveEventArgs $args): void
  {
    $banner->removed_id = $banner->getId();
  }

  public function postRemove(FeaturedBanner $banner, PostRemoveEventArgs $args): void
  {
    if (null === $banner->removed_id) {
      return;
    }

    $this->repository->remove($banner->removed_id, $banner->getImageType(), true);
  }
}
