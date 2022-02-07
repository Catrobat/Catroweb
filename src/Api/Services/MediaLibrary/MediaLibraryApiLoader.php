<?php

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use App\DB\EntityRepository\MediaLibrary\MediaPackageRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MediaLibraryApiLoader extends AbstractApiLoader
{
  private MediaPackageFileRepository $media_package_file_repository;
  private MediaPackageRepository $media_package_repository;
  private EntityManagerInterface $entity_manager;

  public function __construct(MediaPackageFileRepository $media_package_file_repository,
                              MediaPackageRepository $media_package_repository,
                              EntityManagerInterface $entity_manager
  ) {
    $this->media_package_file_repository = $media_package_file_repository;
    $this->media_package_repository = $media_package_repository;
    $this->entity_manager = $entity_manager;
  }

  public function searchMediaLibraryFiles(string $query, string $flavor, string $package_name, int $limit, int $offset): ?array
  {
    return $this->media_package_file_repository->search($query, $flavor, $package_name, $limit, $offset);
  }

  public function getMediaPackageByName(string $name): ?MediaPackage
  {
    return $this->media_package_repository->findOneBy(['nameUrl' => $name]);
  }

  public function getMediaPackageFileByID(int $id): ?MediaPackageFile
  {
    return $this->media_package_file_repository->findOneBy(['id' => $id]);
  }

  public function getMediaPackageFiles(int $limit, int $offset, string $flavor): ?array
  {
    $qb = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from('App\DB\Entity\MediaLibrary\MediaPackageFile', 'f')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    $qb = $this->media_package_file_repository->addFileFlavorsCondition($qb, $flavor, 'f');

    return $qb->getQuery()->getResult();
  }
}
