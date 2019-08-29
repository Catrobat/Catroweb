<?php

namespace App\Entity;

use App\Catrobat\Events\InvalidProgramUploadedEvent;
use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Events\ProgramInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\CatrobatFileSanitizer;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Repository\ExtensionRepository;
use Doctrine\DBAL\Types\GuidType;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;


/**
 * Class ProgramManager
 * @package App\Entity
 */
class ProgramManager
{

  /**
   * @var CatrobatFileExtractor
   */
  protected $file_extractor;

  /**
   * @var CatrobatFileSanitizer
   */
  protected $file_sanitizer;

  /**
   * @var ProgramFileRepository
   */
  protected $file_repository;

  /**
   * @var ScreenshotRepository
   */
  protected $screenshot_repository;

  /**
   * @var EventDispatcherInterface
   */
  protected $event_dispatcher;

  /**
   * @var EntityManager
   */
  protected $entity_manager;

  /**
   * @var ProgramRepository
   */
  protected $program_repository;

  /**
   * @var TagRepository
   */
  protected $tag_repository;

  /**
   * @var ProgramLikeRepository
   */
  protected $program_like_repository;

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var AppRequest
   */
  protected $app_request;

  /**
   * @var ExtensionRepository
   */
  protected $extension_repository;

  /**
   * ProgramManager constructor.
   *
   * @param CatrobatFileExtractor $file_extractor
   * @param ProgramFileRepository $file_repository
   * @param ScreenshotRepository $screenshot_repository
   * @param EntityManager $entity_manager
   * @param ProgramRepository $program_repository
   * @param TagRepository $tag_repository
   * @param ProgramLikeRepository $program_like_repository
   * @param EventDispatcherInterface $event_dispatcher
   * @param LoggerInterface $logger
   * @param AppRequest $app_request
   * @param ExtensionRepository $extension_repository
   * @param CatrobatFileSanitizer $file_sanitizer
   */
  public function __construct(CatrobatFileExtractor $file_extractor, ProgramFileRepository $file_repository,
                              ScreenshotRepository $screenshot_repository, EntityManager $entity_manager,
                              ProgramRepository $program_repository, TagRepository $tag_repository,
                              ProgramLikeRepository $program_like_repository,
                              EventDispatcherInterface $event_dispatcher,
                              LoggerInterface $logger, AppRequest $app_request,
                              ExtensionRepository $extension_repository, CatrobatFileSanitizer $file_sanitizer)
  {
    $this->file_extractor = $file_extractor;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->program_repository = $program_repository;
    $this->tag_repository = $tag_repository;
    $this->program_like_repository = $program_like_repository;
    $this->logger = $logger;
    $this->app_request = $app_request;
    $this->file_sanitizer = $file_sanitizer;
    $this->extension_repository = $extension_repository;
  }

  /**
   * @param AddProgramRequest $request
   *
   * @return Program|null
   * @throws Exception
   */
  public function addProgram(AddProgramRequest $request)
  {
    /**
     * @var $program Program
     * @var $extracted_file ExtractedCatrobatFile
     */
    $file = $request->getProgramfile();

    $extracted_file = $this->file_extractor->extract($file);

    $this->file_sanitizer->sanitize($extracted_file);

    try
    {
      $event = $this->event_dispatcher->dispatch(
        'catrobat.program.before', new ProgramBeforeInsertEvent($extracted_file)
      );
    } catch (InvalidCatrobatFileException $e)
    {
      $this->logger->error($e);
      $this->event_dispatcher->dispatch(
        'catrobat.program.invalid.upload', new InvalidProgramUploadedEvent($file, $e)
      );
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      $this->logger->error("UploadError -> Propagation stopped");

      return null;
    }

    $old_program = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if ($old_program !== null)
    {
      $program = $old_program;
      $this->removeAllTags($program);
      // it's an update
      $program->incrementVersion();
    }
    else
    {
      $program = new Program();
      $program->setRemixRoot(true);
    }
    $program->setName($extracted_file->getName());
    $program->setDescription($extracted_file->getDescription());
    $program->setUser($request->getUser());
    $program->setCatrobatVersion(1);
    $program->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $program->setLanguageVersion($extracted_file->getLanguageVersion());
    $program->setUploadIp($request->getIp());
    $program->setFilesize($file->getSize());
    $program->setVisible(true);
    $program->setApproved(false);
    $program->setUploadLanguage('en');
    $program->setUploadedAt(new DateTime());
    $program->setRemixMigratedAt(null);
    $program->setFlavor($request->getFlavor());
    $program->setDebugBuild($extracted_file->isDebugBuild());
    $this->addTags($program, $extracted_file, $request->getLanguage());

    if ($request->getGamejam() !== null)
    {
      $program->setGamejam($request->getGamejam());
      $program->setGameJamSubmissionDate(new DateTime());
    }

    $this->event_dispatcher->dispatch(
      'catrobat.program.before.persist', new ProgramBeforePersistEvent($extracted_file, $program)
    );

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->addExtensions($program, $extracted_file);

    $this->event_dispatcher->dispatch(
      'catrobat.program.after.insert', new ProgramAfterInsertEvent($extracted_file, $program)
    );


    try
    {
      if ($extracted_file->getScreenshotPath() === null)
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->saveProgramAssetsTemp($extracted_file->getScreenshotPath(), $program->getId());
      }

      $this->file_repository->saveProgramTemp($extracted_file, $program->getId());
    } catch (Exception $e)
    {
      $this->logger->error("UploadError -> saveProgramAssetsTemp failed!", ["exception" => $e]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deleteTempFilesForProgram($program_id);
      } catch (IOException $error)
      {
        $this->logger->error("UploadError -> deleteTempFilesForProgram failed!", ["exception" => $error]);
        throw $error;
      }

      return null;
    }

    try
    {
      if ($extracted_file->getScreenshotPath() === null)
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->makeTempProgramAssetsPerm($program->getId());
      }
      $this->file_repository->makeTempProgramPerm($program->getId());
    } catch (Exception $e)
    {
      $this->logger->error("UploadError -> makeTempProgramPerm failed!", ["exception" => $e]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deletePermProgramAssets($program_id);
        $this->file_repository->deleteProgramFile($program_id);
      } catch (IOException $error)
      {
        $this->logger->error(
          "UploadError -> deletePermProgramAssets or deleteProgramFile failed!", ["exception" => $e]
        );
        throw $error;
      }

      return null;
    }

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->event_dispatcher->dispatch('catrobat.program.successful.upload', new ProgramInsertEvent());

    return $program;
  }

  /**
   * @param $program_id
   * @param $user_id
   *
   * @return mixed
   */
  public function findUserLike($program_id, $user_id)
  {
    return $this->program_like_repository->findOneBy(['program_id' => $program_id, 'user_id' => $user_id]);
  }

  /**
   * @param Program $program
   * @param User    $user
   * @param         $type
   * @param         $no_unlike
   *
   * @return int
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function toggleLike(Program $program, User $user, $type, $no_unlike)
  {
    $existing_program_like = $this->program_like_repository->findOneBy([
      'program_id' => $program->getId(),
      'user_id'    => $user->getId(),
    ]);

    if ($existing_program_like !== null)
    {
      $existing_program_like_type = $existing_program_like->getType();
      $this->entity_manager->remove($existing_program_like);

      // case: unlike
      if ($existing_program_like_type === $type)
      {
        if ($no_unlike)
        {
          return $type;
        }
        $this->entity_manager->flush();

        return ProgramLike::TYPE_NONE;
      }
    }

    // case: like
    $program_like = new ProgramLike($program, $user, $type);
    $this->entity_manager->persist($program_like);
    $this->entity_manager->flush();

    return $type;
  }

  /**
   * @param $program_id
   * @param $type
   *
   * @return mixed
   */
  public function likeTypeCount($program_id, $type)
  {
    return $this->program_like_repository->likeTypeCount($program_id, $type);
  }

  /**
   * @param $program_id
   *
   * @return mixed
   */
  public function totalLikeCount($program_id)
  {
    return $this->program_like_repository->totalLikeCount($program_id);
  }

  /**
   * @param Program               $program
   * @param ExtractedCatrobatFile $extracted_file
   * @param                       $language
   */
  public function addTags(Program $program, ExtractedCatrobatFile $extracted_file, $language)
  {
    /**
     * @var $db_tag Tag
     */
    $metadata = $this->entity_manager->getClassMetadata(Tag::class)->getFieldNames();

    if (!in_array($language, $metadata))
    {
      $language = 'en';
    }

    $tags = $extracted_file->getTags();

    if (!empty($tags))
    {
      $i = 0;
      foreach ($tags as $tag)
      {
        $db_tag = $this->tag_repository->findOneBy([$language => $tag]);

        if ($db_tag !== null)
        {
          $program->addTag($db_tag);
          $i++;
        }

        if ($i === 3)
        {
          break;
        }
      }
    }
  }


  /**
   * @param Program $program
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @throws ORMException
   */
  public function addExtensions(Program $program, ExtractedCatrobatFile $extracted_file)
  {
//     Adding the embroidery extension if an embroidery block was used in the project
    $EMBROIDERY = "Embroidery";
    if ($extracted_file !== null && $extracted_file->getProgramXmlProperties() !== null &&
      strpos($extracted_file->getProgramXmlProperties()->asXML(), '<brick type="StitchBrick">') !== false) {
      /** @var Extension $embroidery_extension */
      $embroidery_extension = $this->extension_repository->findOneBy(['name' => $EMBROIDERY]);
      if ($embroidery_extension === null) {
        $embroidery_extension = new Extension();
        $embroidery_extension->setName($EMBROIDERY);
        $embroidery_extension->setPrefix(strtoupper($EMBROIDERY));
        $this->entity_manager->persist($embroidery_extension);
      }
      $program->addExtension($embroidery_extension);
    }

  }

  /**
   * @param Program $program
   */
  public function removeAllTags(Program $program)
  {
    /* @var $program Program */
    $tags = $program->getTags();
    if ($tags === null)
    {
      return;
    }

    foreach ($tags as $tag)
    {
      $program->removeTag($tag);
    }
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function markAllProgramsAsNotYetMigrated()
  {
    $this->program_repository->markAllProgramsAsNotYetMigrated();
  }

  /**
   * @param $program_name
   * @param $user
   *
   * @return object|null
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByNameAndUser($program_name, $user)
  {
    return $this->program_repository->findOneBy([
      'name' => $program_name,
      'user' => $user,
    ]);
  }

  /**
   * @param $programName
   *
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByName($programName)
  {
    return $this->program_repository->findOneBy(["name" => $programName]);
  }

  /**
   * @param $array
   *
   * @return array|Program[]|StarterCategory[]
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findBy($array)
  {
    return $this->program_repository->findBy($array);
  }

  /**
   * @param GuidType $user_id
   * @param bool $include_debug_build_programs If programs marked as debug_build should be returned
   *
   * @return Program[]
   */
  public function getUserPrograms($user_id, bool $include_debug_build_programs = false)
  {
    $debug_build = ($include_debug_build_programs === true) ?
      true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getUserPrograms($user_id, $debug_build);
  }

  /**
   * @param      $user_id
   * @param bool $include_debug_build_programs If programs marked as debug_build should be returned
   *
   * @return Program[]
   */
  public function getPublicUserPrograms($user_id, bool $include_debug_build_programs = false)
  {
    $debug_build = ($include_debug_build_programs === true) ?
      true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getPublicUserPrograms($user_id, $debug_build);
  }

  /**
   * @return array
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findAll()
  {
    return $this->program_repository->findAll();
  }

  /**
   * @param $previous_program_id
   *
   * @return mixed
   * @throws NonUniqueResultException
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findNext($previous_program_id)
  {
    return $this->program_repository->findNext($previous_program_id);
  }

  /**
   * @param $id
   *
   * @return object|null
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function find($id)
  {
    return $this->program_repository->find($id);
  }

  /**
   * @param $apk_status
   *
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function getProgramsWithApkStatus($apk_status)
  {
    return $this->program_repository->getProgramsWithApkStatus($apk_status);
  }

  /**
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function getProgramsWithExtractedDirectoryHash()
  {
    return $this->program_repository->getProgramsWithExtractedDirectoryHash();
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return Program[]
   */
  public function getRecentPrograms($flavor, $limit = null, $offset = 0)
  {
    return $this->program_repository->getRecentPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return Program[]
   */
  public function getMostViewedPrograms($flavor, $limit = null, $offset = 0)
  {
    return $this->program_repository->getMostViewedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return mixed
   */
  public function getMostDownloadedPrograms($flavor, $limit = null, $offset = 0)
  {
    return $this->program_repository->getMostDownloadedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return array
   */
  public function getRandomPrograms($flavor, $limit = null, $offset = 0)
  {
    return $this->program_repository->getRandomPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string $query The query to search for (search terms)
   * @param int    $limit
   * @param int    $offset
   *
   * @return array
   */
  public function search(string $query, $limit = 10, $offset = 0)
  {
    return $this->program_repository->search($query, $this->app_request->isDebugBuildRequest(), $limit, $offset);
  }

  /**
   * @param string $query The query to search for (search terms)
   *
   * @return int
   */
  public function searchCount(string $query)
  {
    return $this->program_repository->searchCount($query, $this->app_request->isDebugBuildRequest());
  }

  /**
   * @param string|null $flavor
   *
   * @return int
   * @throws NonUniqueResultException
   */
  public function getTotalPrograms($flavor)
  {
    return $this->program_repository->getTotalPrograms($this->app_request->isDebugBuildRequest(), $flavor);
  }

  /**
   * @param Program $program
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseViews(Program $program)
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseDownloads(Program $program)
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseApkDownloads(Program $program)
  {
    $program->setApkDownloads($program->getApkDownloads() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function save(Program $program)
  {
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

  /**
   * @param          $id
   * @param int|null $limit
   * @param int      $offset
   *
   * @return Program[]
   */
  public function getProgramsByTagId($id, $limit, int $offset)
  {
    return $this->program_repository->getProgramsByTagId(
      $id, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
  }

  /**
   * @param string   $name
   * @param int|null $limit
   * @param int      $offset
   *
   * @return mixed
   */
  public function getProgramsByExtensionName(string $name, $limit, int $offset)
  {
    return $this->program_repository->getProgramsByExtensionName(
      $name, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
  }

  /**
   * @param string $query The query to search for (search terms)
   *
   * @return int
   */
  public function searchTagCount(string $query)
  {
    return $this->program_repository->searchTagCount($query, $this->app_request->isDebugBuildRequest());
  }

  /**
   * @param string $query The query to search for (search terms)
   *
   * @return int
   */
  public function searchExtensionCount(string $query)
  {
    return $this->program_repository->searchExtensionCount(
      $query, $this->app_request->isDebugBuildRequest()
    );
  }

  /**
   * @param          $id
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return array
   */
  public function getRecommendedProgramsById($id, string $flavor, $limit, int $offset)
  {
    return $this->program_repository->getRecommendedProgramsById(
      $id, $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param        $id
   * @param string $flavor
   *
   * @return int
   */
  public function getRecommendedProgramsCount($id, string $flavor)
  {
    return $this->program_repository->getRecommendedProgramsCount(
      $id, $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return array
   * @throws DBALException
   */
  public function getMostRemixedPrograms(string $flavor, $limit, int $offset)
  {
    return $this->program_repository->getMostRemixedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string $flavor
   *
   * @return int
   * @throws DBALException
   */
  public function getTotalRemixedProgramsCount(string $flavor)
  {
    return $this->program_repository->getTotalRemixedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return array
   */
  public function getMostLikedPrograms(string $flavor, $limit, int $offset)
  {
    return $this->program_repository->getMostLikedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @param string $flavor
   *
   * @return int
   */
  public function getTotalLikedProgramsCount(string $flavor)
  {
    return $this->program_repository->getTotalLikedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @param string   $flavor
   * @param Program  $program
   * @param int|null $limit
   * @param int      $offset
   * @param bool     $is_test_environment
   *
   * @return array
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
    string $flavor, Program $program, $limit, int $offset, bool $is_test_environment
  )
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $this->app_request->isDebugBuildRequest(), $flavor, $program, $limit, $offset, $is_test_environment
    );
  }

  /**
   * @param string  $flavor
   * @param Program $program
   * @param bool    $is_test_environment
   *
   * @return int
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
    string $flavor, Program $program, bool $is_test_environment
  )
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $program, $is_test_environment
    );
  }

  /**
   * @param $remix_migrated_at
   *
   * @return object|null
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByRemixMigratedAt($remix_migrated_at)
  {
    return $this->program_repository->findOneBy(['remix_migrated_at' => $remix_migrated_at]);
  }
}
