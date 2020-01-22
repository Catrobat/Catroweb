<?php

namespace App\Entity;

use App\Catrobat\Events\InvalidProgramUploadedEvent;
use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Events\ProgramInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AppRequest;
use App\Repository\ExtensionRepository;
use Doctrine\DBAL\Types\GuidType;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use App\Catrobat\Requests\AddMediaFileRequest;
use App\Catrobat\Requests\AddMediaCategoryRequest;
use App\Catrobat\Requests\AddMediaPackageRequest;
use Symfony\Component\HttpFoundation\File\File;


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
   * @param EntityManagerInterface            $entity_manager

   */
  public function __construct(EntityManagerInterface $entity_manager)                        
  {
  
    $this->entity_manager = $entity_manager;
  }

  /**
   * @param AddMediaLibraryRequest $request
   * @throws Exception
   */
  public function addMedia(AddMediaFileRequest $request)
  {
    /**
     * @var $program MediaPackageFile
     */
    $program = new MediaPackageFile();
    $program->setName($request->getName());
    $program->setDownloads(10);
    $program->setCategory($request->getCategory());
    $program->setExtension($request->getExtension());
    $program->setUrl($request->getUrl());
    $program->setActive(true);
    $program->setFlavor($request->getFlavor());
    $program->setAuthor($request->getAuthor());
    $program->setId($request->getId());

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    return $program;
  }

  public function addMediaCategory(AddMediaCategoryRequest $request)
  {
    /**
     * @var $program MediaPackageCategory
     */
    $program = new MediaPackageCategory();
    $program->setName($request->getName());
    $program->setId($request->getId());
    $program->setPriority($request->getPriority());

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    return $program;
  }

  public function addMediaPackage(AddMediaPackageRequest $request)
  {
    /**
     * @var $program MediaPackage
     */
    $program = new MediaPackage();
    $program->setName($request->getName());
    $program->setId($request->getId());
    $program->setNameUrl($request->getNameUrl());

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    return $program;
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