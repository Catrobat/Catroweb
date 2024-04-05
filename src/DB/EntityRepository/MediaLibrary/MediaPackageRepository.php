<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\MediaLibrary;

use App\DB\Entity\MediaLibrary\MediaPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class MediaPackageRepository used for interacting with the database when handling MediaPackages.
 */
class MediaPackageRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $manager_registry)
  {
    parent::__construct($manager_registry, MediaPackage::class);
  }

  /**
   * Creates a new MediaPackage.
   *
   * @param string $name The name
   * @param string $url  The url under which it can be addressed. Only the last part of the url must be specified. E.g.
   *                     for a MediaPackage with name "Animals" use url "animals".
   *
   * @return MediaPackage the created MediaPackage
   *
   * @throws \Exception when an error occurs during creating
   */
  public function createMediaPackage(string $name, string $url): MediaPackage
  {
    $new_media_package = new MediaPackage();
    $new_media_package->setName($name);
    $new_media_package->setNameUrl($url);

    $this->getEntityManager()->persist($new_media_package);
    $this->getEntityManager()->flush();

    return $new_media_package;
  }
}
