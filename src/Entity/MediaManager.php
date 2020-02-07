<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Catrobat\Requests\AddMediaFileRequest;
use App\Catrobat\Requests\AddMediaCategoryRequest;
use App\Catrobat\Requests\AddMediaPackageRequest;


/**
 * Class MediaManager
 * @package App\Entity
 */
class MediaManager
{
  /**
   * @var EntityManagerInterface
   */
  protected $entity_manager;


  /**
   * MediaManager constructor.
   *
   * @param EntityManagerInterface $entity_manager
   */
  public function __construct(EntityManagerInterface $entity_manager)
  {

    $this->entity_manager = $entity_manager;
  }

  /**
   * @param AddMediaFileRequest $request
   *
   * @return MediaPackageFile
   */
  public function addMedia(AddMediaFileRequest $request)
  {
    /**
     * @var $media_package_file MediaPackageFile
     */
    $media_package_file = new MediaPackageFile();
    $media_package_file->setName($request->getName());
    $media_package_file->setDownloads(10);
    $media_package_file->setCategory($request->getCategory());
    $media_package_file->setExtension($request->getExtension());
    $media_package_file->setUrl($request->getUrl());
    $media_package_file->setActive(true);
    $media_package_file->setFlavor($request->getFlavor());
    $media_package_file->setAuthor($request->getAuthor());
    $media_package_file->setId($request->getId());

    $this->entity_manager->persist($media_package_file);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($media_package_file);

    return $media_package_file;
  }

  public function addMediaCategory(AddMediaCategoryRequest $request)
  {
    /**
     * @var $media_package_category MediaPackageCategory
     */
    $media_package_category = new MediaPackageCategory();
    $media_package_category->setName($request->getName());
    $media_package_category->setId($request->getId());
    $media_package_category->setPriority($request->getPriority());

    $this->entity_manager->persist($media_package_category);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($media_package_category);

    return $media_package_category;
  }

  public function addMediaPackage(AddMediaPackageRequest $request)
  {
    /**
     * @var $media_package MediaPackage
     */
    $media_package = new MediaPackage();
    $media_package->setName($request->getName());
    $media_package->setId($request->getId());
    $media_package->setNameUrl($request->getNameUrl());

    $this->entity_manager->persist($media_package);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($media_package);

    return $media_package;
  }

  public function findCategory($media)
  {
    /**
     * @var $category int
     */
    $category = $this->entity_manager->getRepository(MediaPackageCategory::class)->findOneByName($media['category']);

    return $category;
  }
}