<?php

namespace App\Entity;

use App\Catrobat\Events\InvalidProgramUploadedEvent;
use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\CatrobatFileSanitizer;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Repository\ExampleRepository;
use App\Repository\ExtensionRepository;
use App\Repository\FeaturedRepository;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use Elastica\Util;
use Exception;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\UserBundle\Model\UserInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\UrlHelper;

class ProgramManager
{
  protected CatrobatFileExtractor $file_extractor;

  protected CatrobatFileSanitizer $file_sanitizer;

  protected ProgramFileRepository $file_repository;

  protected ScreenshotRepository $screenshot_repository;

  protected EventDispatcherInterface $event_dispatcher;

  protected EntityManagerInterface $entity_manager;

  protected ProgramRepository $program_repository;

  protected TagRepository $tag_repository;

  protected ProgramLikeRepository $program_like_repository;

  protected FeaturedRepository $featured_repository;

  protected ExampleRepository $example_repository;

  protected AppRequest $app_request;

  protected CatroNotificationService $notification_service;

  protected ExtensionRepository $extension_repository;

  private LoggerInterface $logger;

  private ?UrlHelper $urlHelper;

  private TransformedFinder $program_finder;

  public function __construct(CatrobatFileExtractor $file_extractor, ProgramFileRepository $file_repository,
                              ScreenshotRepository $screenshot_repository, EntityManagerInterface $entity_manager,
                              ProgramRepository $program_repository, TagRepository $tag_repository,
                              ProgramLikeRepository $program_like_repository,
                              FeaturedRepository $featured_repository,
                              ExampleRepository $example_repository,
                              EventDispatcherInterface $event_dispatcher,
                              LoggerInterface $logger, AppRequest $app_request,
                              ExtensionRepository $extension_repository, CatrobatFileSanitizer $file_sanitizer,
                              CatroNotificationService $notification_service, TransformedFinder $program_finder,
                              UrlHelper $url_helper = null)
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
    $this->example_repository = $example_repository;
    $this->logger = $logger;
    $this->app_request = $app_request;
    $this->file_sanitizer = $file_sanitizer;
    $this->extension_repository = $extension_repository;
    $this->notification_service = $notification_service;
    $this->program_finder = $program_finder;
    $this->urlHelper = $url_helper;
  }

  public function getFeaturedRepository(): FeaturedRepository
  {
    return $this->featured_repository;
  }

  public function getExampleRepository(): ExampleRepository
  {
    return $this->example_repository;
  }

  /**
   * Check visibility of the given project for the current user.
   *
   * @throws NoResultException
   */
  public function isProjectVisibleForCurrentUser(?Program $project): bool
  {
    if (null === $project)
    {
      return false;
    }

    if (!$project->isVisible())
    {
      // featured or approved projects should never be invisible
      if (!$this->featured_repository->isFeatured($project) && !$project->getApproved())
      {
        return false;
      }
    }

    // SHARE-49: Private projects are visible to everyone.
    // -

    // SHARE-70/SHARE-296: Debug projects must only be seen in the dev env or if explicitly requested
    if ($project->isDebugBuild() && !$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV'])
    {
      return false;
    }

    return true;
  }

  /**
   * Adds a new program and notifies all followers of the uploader about it.
   *
   * @throws Exception
   */
  public function addProgram(AddProgramRequest $request): ?Program
  {
    $file = $request->getProgramFile();

    $extracted_file = $this->file_extractor->extract($file);

    $this->file_sanitizer->sanitize($extracted_file);

    try
    {
      $event = $this->event_dispatcher->dispatch(new ProgramBeforeInsertEvent($extracted_file));
    }
    catch (InvalidCatrobatFileException $e)
    {
      $this->logger->error('addProgram failed with code: '.$e->getCode().' and message:'.$e->getMessage());
      $this->event_dispatcher->dispatch(new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      $this->logger->error('UploadError -> Propagation stopped');

      return null;
    }

    /** @var Program|null $old_program */
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
    $program->setUploadedAt(TimeUtils::getDateTime());
    $program->setRemixMigratedAt(null);
    $program->setFlavor($request->getFlavor());
    $program->setDebugBuild($extracted_file->isDebugBuild());
    $this->addTags($program, $extracted_file, $request->getLanguage());

    $this->event_dispatcher->dispatch(new ProgramBeforePersistEvent($extracted_file, $program));

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->addExtensions($program, $extracted_file);

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
    $zip_exists = $this->file_repository->checkIfProgramFileExists($program->getId());
    $this->file_repository->saveProgramFile($file, $program->getId());

    $this->event_dispatcher->dispatch(new ProgramAfterInsertEvent($extracted_file, $program));
    $this->notifyFollower($program);
    $compressed_file_directory = $this->file_extractor->getExtractDir().'/'.$program->getId();
    if (is_dir($compressed_file_directory))
    {
      (new Filesystem())->remove($compressed_file_directory);
    }
    if (is_dir($extracted_file->getPath()))
    {
      (new Filesystem())->rename($extracted_file->getPath(), $this->file_extractor->getExtractDir().'/'.$program->getId());
    }
    (new Filesystem())->remove($extracted_file->getPath());
    if (!$zip_exists)
    {
      $this->file_repository->deleteProgramFile($program->getId());
    }

    return $program;
  }

  /**
   * Adds a new program from a scratch_program. Doesn't add the Project file.
   */
  public function createProgramFromScratch(?Program $program, User $user, array $program_data): Program
  {
    $modified_time = TimeUtils::dateTimeFromScratch($program_data['history']['modified']);
    if (null === $program)
    {
      $program = new Program();
      $program->setUser($user);
      $program->setScratchId($program_data['id']);
      $program->setDebugBuild(false);
    }
    else
    {
      //throw new Exception($program->getLastModifiedAt()->format('Y-m-d H:i:s'));
      if ($program->getLastModifiedAt()->getTimestamp() > $modified_time->getTimestamp())
      {
        return $program;
      }
      $program->incrementVersion();
    }
    $program->setCatrobatVersion(1);
    $program->setVisible(true);
    $program->setApproved(false);

    $description_text = '';
    if ($instructions = $program_data['instructions'] ?? null)
    {
      $description_text .= $instructions;
    }
    if ($description = $program_data['description'] ?? null)
    {
      if ($instructions)
      {
        $description_text .= "\n\n";
      }
      $description_text .= $description;
    }
    $program->setDescription($description_text);

    if ($title = $program_data['title'] ?? null)
    {
      $program->setName($title);
    }

    $shared_time = TimeUtils::dateTimeFromScratch($program_data['history']['shared']);
    if ($shared_time)
    {
      $program->setUploadedAt($shared_time);
    }
    else
    {
      $program->setUploadedAt(TimeUtils::getDateTime());
    }
    if ($modified_time)
    {
      $program->setLastModifiedAt($modified_time);
    }
    else
    {
      $program->setLastModifiedAt(TimeUtils::getDateTime());
    }

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->notifyFollower($program);

    if ($image_url = $program_data['image'] ?? null)
    {
      $this->screenshot_repository->saveScratchScreenshot($program->getScratchId(), $program->getId());
    }

    return $program;
  }

  /**
   * @return ProgramLike[]
   */
  public function findUserLikes(string $project_id, string $user_id)
  {
    return $this->program_like_repository->findBy(['program_id' => $project_id, 'user_id' => $user_id]);
  }

  public function findProgramLikeTypes(string $project_id): array
  {
    return $this->program_like_repository->likeTypesOfProject($project_id);
  }

  /**
   * @throws InvalidArgumentException
   * @throws ORMException
   */
  public function changeLike(Program $project, User $user, int $type, string $action): void
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
   * @throws NoResultException
   */
  public function areThereOtherLikeTypes(Program $project, User $user, int $type): bool
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

  public function likeTypeCount(string $program_id, int $type): int
  {
    return $this->program_like_repository->likeTypeCount($program_id, $type);
  }

  public function totalLikeCount(string $program_id): int
  {
    return $this->program_like_repository->totalLikeCount($program_id);
  }

  /**
   * @param mixed $language
   */
  public function addTags(Program $program, ExtractedCatrobatFile $extracted_file, $language): void
  {
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
        /** @var Tag|null $db_tag */
        $db_tag = $this->tag_repository->findOneBy([$language => $tag]);

        if (null !== $db_tag)
        {
          $program->addTag($db_tag);
          ++$i;
        }

        if (3 === $i)
        {
          // Only 3 tags at once!
          break;
        }
      }
    }
  }

  /**
   * Adding the embroidery extension if an embroidery block was used in the project.
   */
  public function addExtensions(Program $program, ExtractedCatrobatFile $extracted_file): void
  {
    $EMBROIDERY = 'Embroidery';
    if (false !== strpos($extracted_file->getProgramXmlProperties()->asXML(), '<brick type="StitchBrick">'))
    {
      /** @var Extension|null $embroidery_extension */
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

  public function removeAllTags(Program $program): void
  {
    $tags = $program->getTags();

    foreach ($tags as $tag)
    {
      $program->removeTag($tag);
    }
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function markAllProgramsAsNotYetMigrated(): void
  {
    $this->program_repository->markAllProgramsAsNotYetMigrated();
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return Program|object|null
   */
  public function findOneByNameAndUser(string $program_name, UserInterface $user)
  {
    return $this->program_repository->findOneBy([
      'name' => $program_name,
      'user' => $user,
    ]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return Program|object|null
   */
  public function findOneByName(string $programName)
  {
    return $this->program_repository->findOneBy(['name' => $programName]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return Program|object|null
   */
  public function findOneByScratchId(int $scratch_id)
  {
    return $this->program_repository->findOneBy(['scratch_id' => $scratch_id]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null): array
  {
    return $this->program_repository->findBy($criteria, $orderBy, $limit, $offset);
  }

  public function getUserProjects(string $username, ?int $limit = 20, int $offset = 0, ?string $flavor = null, string $max_version = '0'): array
  {
    return $this->program_repository->getUserProjects(
      $username, $limit, $offset, $flavor, $this->app_request->isDebugBuildRequest(), $max_version);
  }

  public function getUserProjectsCount(string $username, ?string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getUserProjectsCount(
      $username, $flavor, $this->app_request->isDebugBuildRequest(), $max_version);
  }

  /**
   * @return Program[]
   */
  public function getUserPrograms(string $user_id, bool $include_debug_build_programs = false, string $max_version = '0'): array
  {
    $debug_build = (true === $include_debug_build_programs) ? true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getUserPrograms($user_id, $debug_build, $max_version);
  }

  /**
   * @return Program[]
   */
  public function getPublicUserPrograms(string $user_id, bool $include_debug_build_programs = false, string $max_version = '0'): array
  {
    $debug_build = (true === $include_debug_build_programs) ?
      true : $this->app_request->isDebugBuildRequest();

    return $this->program_repository->getPublicUserPrograms($user_id, $debug_build, $max_version);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findAll(): array
  {
    return $this->program_repository->findAll();
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   *
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return mixed
   */
  public function findNext(string $previous_program_id)
  {
    return $this->program_repository->findNext($previous_program_id);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return Program|object|null
   */
  public function find(string $id)
  {
    return $this->program_repository->find($id);
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

  public function getRecentPrograms(string $flavor = null, int $limit = 20, int $offset = 0,
                                    string $max_version = '0'): array
  {
    return $this->program_repository->getRecentPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getRecentProgramsCount(string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getRecentProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getMostViewedPrograms(string $flavor = null, int $limit = 20, int $offset = 0,
                                        string $max_version = '0'): array
  {
    return $this->program_repository->getMostViewedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getExamplePrograms(?string $flavor = null, ?int $limit = null, int $offset = 0, string $max_version = '0'): array
  {
    return $this->example_repository->getExamplePrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getExampleProgramsCount(?string $flavor = null, string $max_version = '0'): int
  {
    return $this->example_repository->getExampleProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getMostViewedProgramsCount(string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getMostViewedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getScratchRemixesPrograms(string $flavor = null, int $limit = 20, int $offset = 0,
                                            string $max_version = '0'): array
  {
    return $this->program_repository->getScratchRemixesPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getScratchRemixesProgramsCount(string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getScratchRemixesProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getMostDownloadedPrograms(string $flavor = null, int $limit = 20, int $offset = 0,
                                            string $max_version = '0'): array
  {
    return $this->program_repository->getMostDownloadedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getMostDownloadedProgramsCount(string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getMostDownloadedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getRandomPrograms(string $flavor = null, int $limit = 20, int $offset = 0,
                                    string $max_version = '0'): array
  {
    return $this->program_repository->getRandomPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getRandomProgramsCount(string $flavor = null, string $max_version = '0'): int
  {
    return $this->program_repository->getRandomProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function search(string $query, ?int $limit = 10, int $offset = 0, string $max_version = '0', ?string $flavor = null, bool $is_debug_request = false): array
  {
    $program_query = $this->programSearchQuery($query, $max_version, $flavor, $is_debug_request);

    return $this->program_finder->find($program_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query, string $max_version = '0', ?string $flavor = null, bool $is_debug_request = false): int
  {
    $program_query = $this->programSearchQuery($query, $max_version, $flavor, $is_debug_request);

    $paginator = $this->program_finder->findPaginated($program_query);

    return $paginator->getNbResults();
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function getTotalPrograms(?string $flavor, string $max_version = '0'): int
  {
    return $this->program_repository->getTotalPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function increaseViews(Program $program): void
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  public function increaseDownloads(Program $program): void
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  public function increaseApkDownloads(Program $program): void
  {
    $program->setApkDownloads($program->getApkDownloads() + 1);
    $this->save($program);
  }

  public function save(Program $program): void
  {
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

  public function getProgramsByTagId(int $id, ?int $limit, int $offset): array
  {
    return $this->program_repository->getProgramsByTagId(
      $id, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
  }

  public function getProgramsByExtensionName(string $name, ?int $limit, int $offset): array
  {
    return $this->program_repository->getProgramsByExtensionName(
      $name, $this->app_request->isDebugBuildRequest(), $limit, $offset
    );
  }

  public function searchTagCount(int $tag_id): int
  {
    return $this->program_repository->searchTagCount($tag_id, $this->app_request->isDebugBuildRequest());
  }

  public function searchExtensionCount(string $query): int
  {
    return $this->program_repository->searchExtensionCount(
      $query, $this->app_request->isDebugBuildRequest()
    );
  }

  public function getRecommendedProgramsById(string $id, string $flavor, ?int $limit, int $offset): array
  {
    return $this->program_repository->getRecommendedProgramsById(
      $id, $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  public function getRecommendedProgramsCount(string $id, string $flavor): int
  {
    return $this->program_repository->getRecommendedProgramsCount(
      $id, $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  /**
   * @throws DBALException
   */
  public function getMostRemixedPrograms(string $flavor, ?int $limit, int $offset): array
  {
    return $this->program_repository->getMostRemixedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  /**
   * @throws DBALException
   */
  public function getTotalRemixedProgramsCount(string $flavor): int
  {
    return $this->program_repository->getTotalRemixedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  public function getMostLikedPrograms(string $flavor, ?int $limit, int $offset): array
  {
    return $this->program_repository->getMostLikedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset
    );
  }

  public function getTotalLikedProgramsCount(string $flavor): int
  {
    return $this->program_repository->getTotalLikedProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor
    );
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
    string $flavor, Program $program, ?int $limit, int $offset): array
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $this->app_request->isDebugBuildRequest(), $flavor, $program, $limit, $offset
    );
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
    string $flavor, Program $program): int
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $program
    );
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   *
   * @return Program|object|null
   */
  public function findOneByRemixMigratedAt(?DateTime $remix_migrated_at)
  {
    return $this->program_repository->findOneBy(['remix_migrated_at' => $remix_migrated_at]);
  }

  public function getUserPublicPrograms(string $user_id, ?int $limit = 20, int $offset = 0,
                                        string $flavor = null, string $max_version = '0'): array
  {
    return $this->program_repository->getUserPublicPrograms(
      $user_id, $this->app_request->isDebugBuildRequest(), $max_version, $limit, $offset, $flavor);
  }

  public function getUserPublicProgramsCount(string $user_id, string $flavor = null,
                                             string $max_version = '0'): int
  {
    return $this->program_repository->getUserPublicProgramsCount(
      $user_id, $this->app_request->isDebugBuildRequest(), $max_version, $flavor);
  }

  public function getScreenshotLarge(string $id): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->screenshot_repository->getScreenshotWebPath($id);
  }

  public function getScreenshotSmall(string $id): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->screenshot_repository->getThumbnailWebPath($id);
  }

  public function decodeToken(string $token): array
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1], true);

    return json_decode($tokenPayload, true);
  }

  public function getProjects(string $category, string $max_version = '0',
                              int $limit = 20, int $offset = 0, string $flavor = null): array
  {
    switch ($category){
      case 'recent':
        return $this->getRecentPrograms($flavor, $limit, $offset, $max_version);
      case 'random':
        return $this->getRandomPrograms($flavor, $limit, $offset, $max_version);
      case 'most_viewed':
        return $this->getMostViewedPrograms($flavor, $limit, $offset, $max_version);
      case 'most_downloaded':
        return $this->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
      case 'example':
        return $this->getExamplePrograms($flavor, $limit, $offset, $max_version);
      case 'scratch':
        return $this->getScratchRemixesPrograms($flavor, $limit, $offset, $max_version);
      default:
        return [];
    }
  }

  public function getProjectsCount(string $category, string $max_version = '0', string $flavor = null): int
  {
    switch ($category){
      case 'recent':
        return $this->getRecentProgramsCount($flavor, $max_version);
      case 'random':
        return $this->getRandomProgramsCount($flavor, $max_version);
      case 'most_viewed':
        return $this->getMostViewedProgramsCount($flavor, $max_version);
      case 'most_downloaded':
        return $this->getMostDownloadedProgramsCount($flavor, $max_version);
      case 'example':
        return $this->getExampleProgramsCount($flavor, $max_version);
      case 'scratch':
        return $this->getScratchRemixesProgramsCount($flavor, $max_version);
      default:
        return 0;
    }
  }

  public function parseAcceptLanguage(string $acceptLanguage): array
  {
    $prefLocales = array_reduce(
      explode(',', $acceptLanguage),
      function ($res, $el)
      {
        [$l, $q] = array_merge(explode(';q=', $el), [1]);
        array_push($res, $l);

        return $res;
      }, []);
    arsort($prefLocales);

    return $prefLocales;
  }

  public function getProgram(string $id): array
  {
    return $this->program_repository->getProgram(
      $id, $this->app_request->isDebugBuildRequest()
    );
  }

  private function programSearchQuery(string $query, string $max_version = '0', ?string $flavor = null, bool $is_debug_request = false): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word)
    {
      $word = $word.'*';
    }
    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'name', 'description', 'getUsernameString', 'getTagsString', 'getExtensionsString']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $category_query[] = new Terms('private', [false]);
    $category_query[] = new Terms('visible', [true]);

    if (!$is_debug_request)
    {
      $category_query[] = new Terms('debug_build', [false]);
    }

    if ('0' !== $max_version)
    {
      $category_query[] = new Range('language_version', ['lte' => $max_version]);
    }
    if (null !== $flavor)
    {
      $category_query[] = new Terms('flavor', [$flavor]);
    }

    $bool_query = new BoolQuery();
    $bool_query->addMust($category_query);
    $bool_query->addMust($query_string);

    return $bool_query;
  }

  private function notifyFollower(Program $program): void
  {
    $followers = $program->getUser()->getFollowers();
    for ($i = 0; $i < $followers->count(); ++$i)
    {
      $notification = new NewProgramNotification($followers[$i], $program);
      $this->notification_service->addNotification($notification);
    }
  }
}
