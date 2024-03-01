<?php

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use App\DB\EntityRepository\MediaLibrary\MediaPackageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MediaLibraryApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly MediaPackageFileRepository $media_package_file_repository, private readonly MediaPackageRepository $media_package_repository, private readonly EntityManagerInterface $entity_manager)
  {
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
      ->from(MediaPackageFile::class, 'f')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    $qb = $this->media_package_file_repository->addFileFlavorsCondition($qb, $flavor, 'f');

    return $qb->getQuery()->getResult();
  }
}
