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
use App\Repository\FeaturedRepository;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use App\Utils\TimeUtils;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class ProgramManager.
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
   * @var EntityManagerInterface
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

  /** @var FeaturedRepository */
  protected $featured_repository;

  /**
   * @var AppRequest
   */
  protected $app_request;

  /**
   * @var ExtensionRepository
   */
  protected $extension_repository;

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * ProgramManager constructor.
   */
  public function __construct(CatrobatFileExtractor $file_extractor, ProgramFileRepository $file_repository,
                              ScreenshotRepository $screenshot_repository, EntityManagerInterface $entity_manager,
                              ProgramRepository $program_repository, TagRepository $tag_repository,
                              ProgramLikeRepository $program_like_repository,
                              FeaturedRepository $featured_repository,
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
    $this->featured_repository = $featured_repository;
    $this->logger = $logger;
    $this->app_request = $app_request;
    $this->file_sanitizer = $file_sanitizer;
    $this->extension_repository = $extension_repository;
  }

  /**
   * @return FeaturedRepository
   */
  public function getFeaturedRepository()
  {
    return $this->featured_repository;
  }

  /**
   * Check visibility of the given project for the current user.
   *
   * @return bool
   */
  public function isProjectVisibleForCurrentUser(Program $project)
  {
    if (!$project)
    {
      return false;
    }

    if (!$project->isVisible())
    {
      if (!$this->featured_repository->isFeatured($project) || !$project->getApproved())
      {
        return false;
      }
    }

//    Right now everyone should find even private programs via the correct link! SHARE-49
//    if ($program->getPrivate() && $program->getUser()->getId() !== $this->getUser()->getId()) {
//      // only program owners should be allowed to see their programs
//      return false;
//    }

    if ($project->isDebugBuild())
    {
      if (!$this->app_request->isDebugBuildRequest())
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @throws Exception
   *
   * @return Program|null
   */
  public function addProgram(AddProgramRequest $request)
  {
    /**
     * @var Program
     * @var ExtractedCatrobatFile $extracted_file
     */
    $file = $request->getProgramfile();

    $extracted_file = $this->file_extractor->extract($file);

    $this->file_sanitizer->sanitize($extracted_file);

    try
    {
      $event = $this->event_dispatcher->dispatch(new ProgramBeforeInsertEvent($extracted_file));
    }
    catch (InvalidCatrobatFileException $e)
    {
      $this->logger->error($e);
      $this->event_dispatcher->dispatch(new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      $this->logger->error('UploadError -> Propagation stopped');

      return null;
    }

    /** @var Program $old_program */
    $old_program = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if (null !== $old_program)
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
    $program->setUploadedAt(new DateTime('now', new DateTimeZone('UTC')));
    $program->setRemixMigratedAt(null);
    $program->setFlavor($request->getFlavor());
    $program->setDebugBuild($extracted_file->isDebugBuild());
    $this->addTags($program, $extracted_file, $request->getLanguage());

    if (null !== $request->getGamejam())
    {
      $program->setGamejam($request->getGamejam());
      $program->setGameJamSubmissionDate(TimeUtils::getDateTime());
    }

    $this->event_dispatcher->dispatch(new ProgramBeforePersistEvent($extracted_file, $program));

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->addExtensions($program, $extracted_file);

    $this->event_dispatcher->dispatch(new ProgramAfterInsertEvent($extracted_file, $program));

    try
    {
      if (null === $extracted_file->getScreenshotPath())
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->saveProgramAssetsTemp($extracted_file->getScreenshotPath(), $program->getId());
      }

      $this->file_repository->saveProgramTemp($extracted_file, $program->getId());
    }
    catch (Exception $e)
    {
      $this->logger->error('UploadError -> saveProgramAssetsTemp failed!', ['exception' => $e->getMessage()]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deleteTempFilesForProgram($program_id);
      }
      catch (IOException $error)
      {
        $this->logger->error('UploadError -> deleteTempFilesForProgram failed!', ['exception' => $error]);
        throw $error;
      }

      return null;
    }

    try
    {
      if (null === $extracted_file->getScreenshotPath())
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->makeTempProgramAssetsPerm($program->getId());
      }
      $this->file_repository->makeTempProgramPerm($program->getId());
    }
    catch (Exception $e)
    {
      $this->logger->error('UploadError -> makeTempProgramPerm failed!', ['exception' => $e]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deletePermProgramAssets($program_id);
        $this->file_repository->deleteProgramFile($program_id);
      }
      catch (IOException $error)
      {
        $this->logger->error(
          'UploadError -> deletePermProgramAssets or deleteProgramFile failed!', ['exception' => $e]
        );
        throw $error;
      }

      return null;
    }

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->event_dispatcher->dispatch(new ProgramInsertEvent());

    return $program;
  }

  /**
   * @param $project_id
   * @param $user_id
   *
   * @return ProgramLike[]
   */
  public function findUserLikes($project_id, $user_id)
  {
    return $this->program_like_repository->findBy(['program_id' => $project_id, 'user_id' => $user_id]);
  }

  /**
   * @param $project_id
   *
   * @return array
   */
  public function findProgramLikeTypes($project_id)
  {
    return $this->program_like_repository->likeTypesOfProject($project_id);
  }

  /**
   * @param        $type
   * @param string $action
   *
   * @throws InvalidArgumentException
   * @throws ORMException
   */
  public function changeLike(Program $project, User $user, $type, $action)
  {
    if (ProgramLike::ACTION_ADD === $action)
    {
      $this->program_like_repository->addLike($project, $user, $type);
    }
    elseif (ProgramLike::ACTION_REMOVE === $action)
    {
      $this->program_like_repository->removeLike($project, $user, $type);
    }
    else
    {
      throw new InvalidArgumentException('Invalid action "'.$action.'"');
    }
  }

  /**
   * @param $type
   *
   * @return bool
   */
  public function areThereOtherLikeTypes(Program $project, User $user, $type)
  {
    try
    {
      return $this->program_like_repository->areThereOtherLikeTypes($project, $user, $type);
    }
    catch (NonUniqueResultException $exception)
    {
      return false;
    }
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
   * @param $language
   */
  public function addTags(Program $program, ExtractedCatrobatFile $extracted_file, $language)
  {
    /**
     * @var Tag
     */
    $metadata = $this->entity_manager->getClassMetadata(Tag::class)->getFieldNames();

    if (!in_array($language, $metadata, true))
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

        if (null !== $db_tag)
        {
          $program->addTag($db_tag);
          ++$i;
        }

        if (3 === $i)
        {
          break;
        }
      }
    }
  }

  public function addExtensions(Program $program, ExtractedCatrobatFile $extracted_file)
  {
//     Adding the embroidery extension if an embroidery block was used in the project
    $EMBROIDERY = 'Embroidery';
    if (null !== $extracted_file && null !== $extracted_file->getProgramXmlProperties() &&
      false !== strpos($extracted_file->getProgramXmlProperties()->asXML(), '<brick type="StitchBrick">'))
    {
      /** @var Extension $embroidery_extension */
      $embroidery_extension = $this->extension_repository->findOneBy(['name' => $EMBROIDERY]);
      if (null === $embroidery_extension)
      {
        $embroidery_extension = new Extension();
        $embroidery_extension->setName($EMBROIDERY);
        $embroidery_extension->setPrefix(strtoupper($EMBROIDERY));
        $this->entity_manager->persist($embroidery_extension);
      }
      $program->addExtension($embroidery_extension);
    }
  }

  public function removeAllTags(Program $program)
  {
    /* @var $program Program */
    $tags = $program->getTags();
    if (null === $tags)
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
    return $this->program_repository->findOneBy(['name' => $programName]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @param null $limit
   * @param null $offset
   *
   * @return array|Program[]|StarterCategory[]
   * @return array
   */
  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
  {
    return $this->program_repository->findBy($criteria);
  }

  /**
   * @param GuidType $user_id
   * @param bool     $include_debug_build_programs If programs marked as debug_build should be returned
   *
   * @return Program[]
   */
  public function getUserPrograms($user_id, bool $include_debug_build_programs = false, string $max_version = '0')
  {
    $debug_build = (true === $include_debug_build_programs) ? true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getUserPrograms($user_id, $debug_build, $max_version);
  }

  /**
   * @param      $user_id
   * @param bool $include_debug_build_programs If programs marked as debug_build should be returned
   *
   * @return Program[]
   */
  public function getPublicUserPrograms($user_id, bool $include_debug_build_programs = false, string $max_version = '0')
  {
    $debug_build = (true === $include_debug_build_programs) ?
      true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getPublicUserPrograms($user_id, $debug_build, $max_version);
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
   * @throws NonUniqueResultException
   *
   * @return mixed
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
  public function getRecentPrograms($flavor, $limit = null, $offset = 0, string $max_version = '0')
  {
    return $this->program_repository->getRecentPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return Program[]
   */
  public function getMostViewedPrograms($flavor, $limit = null, $offset = 0, string $max_version = '0')
  {
    return $this->program_repository->getMostViewedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return Program[]
   */
  public function getScratchRemixesPrograms($flavor, $limit = null, $offset = 0)
  {
    return $this->program_repository->getScratchRemixesPrograms(
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
  public function getMostDownloadedPrograms($flavor, $limit = null, $offset = 0, string $max_version = '0')
  {
    return $this->program_repository->getMostDownloadedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  /**
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   *
   * @return array
   */
  public function getRandomPrograms($flavor, $limit = null, $offset = 0, string $max_version = '0')
  {
    return $this->program_repository->getRandomPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  /**
   * @param int $limit
   * @param int $offset
   *
   * @throws Exception
   *
   * @return array
   */
  public function search(string $query, $limit = 10, $offset = 0, string $max_version = '0')
  {
    return $this->program_repository->search(
      $query, $this->app_request->isDebugBuildRequest(), $limit, $offset, $max_version
    );
  }

  /**
   * @param string $query The query to search for (search terms)
   *
   * @return int
   */
  public function searchCount(string $query, string $max_version = '0')
  {
    return $this->program_repository->searchCount($query, $this->app_request->isDebugBuildRequest(), $max_version);
  }

  /**
   * @param $flavor
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return int
   */
  public function getTotalPrograms($flavor, string $max_version = '0')
  {
    return $this->program_repository->getTotalPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseViews(Program $program)
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseDownloads(Program $program)
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function increaseApkDownloads(Program $program)
  {
    $program->setApkDownloads($program->getApkDownloads() + 1);
    $this->save($program);
  }

  /**
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
   * @param int|null $limit
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
   * @param int|null $limit
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
   * @param $id
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
   * @param int|null $limit
   *
   * @throws DBALException
   *
   * @return array
   */
  public function getMostRemixedPrograms(string $flavor, $limit, int $offset)
  {
    return $this->program_repository->getMostRemixedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @throws DBALException
   *
   * @return int
   */
  public function getTotalRemixedProgramsCount(string $flavor)
  {
    return $this->program_repository->getTotalRemixedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @param int|null $limit
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
   * @return int
   */
  public function getTotalLikedProgramsCount(string $flavor)
  {
    return $this->program_repository->getTotalLikedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @param int|null $limit
   *
   * @return array
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
    string $flavor, Program $program, $limit, int $offset, bool $is_test_environment
  ) {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $this->app_request->isDebugBuildRequest(), $flavor, $program, $limit, $offset, $is_test_environment
    );
  }

  /**
   * @return int
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
    string $flavor, Program $program, bool $is_test_environment
  ) {
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
