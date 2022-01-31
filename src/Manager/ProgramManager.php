<?php

namespace App\Manager;

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
use App\Entity\NewProgramNotification;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\ExampleRepository;
use App\Repository\ExtensionRepository;
use App\Repository\FeaturedRepository;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use App\Utils\TimeUtils;
use DateTime;
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
use Symfony\Component\Security\Core\Security;

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

  protected Security $security;

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
                              UrlHelper $url_helper = null, Security $security)
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
    $this->security = $security;
  }

  public function getFeaturedRepository(): FeaturedRepository
  {
    return $this->featured_repository;
  }

  public function getExampleRepository(): ExampleRepository
  {
    return $this->example_repository;
  }

  public function getProjectByID(string $id, bool $include_private = false): array
  {
    return $this->program_repository->getProjectByID($id, $include_private);
  }

  /**
   * Check visibility of the given project for the current user.
   */
  protected function isProjectVisibleForCurrentUser(Program $project): bool
  {
    /** @var User|null $user */
    $user = $this->security->getUser();
    if (null !== $user && $user->isSuperAdmin()) {
      return true;
    }

    if (!$project->isVisible()) {
      // featured or approved projects should never be invisible
      if (!$this->featured_repository->isFeatured($project) && !$project->getApproved()) {
        return false;
      }
    }

    // SHARE-49: Private projects are visible to everyone.
    // -

    // SHARE-70/SHARE-296: Debug projects must only be seen in the dev env or if explicitly requested
    if ($project->isDebugBuild() && !$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV']) {
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

    try {
      $event = $this->event_dispatcher->dispatch(new ProgramBeforeInsertEvent($extracted_file));
    } catch (InvalidCatrobatFileException $e) {
      $this->logger->error('addProgram failed with code: '.$e->getCode().' and message:'.$e->getMessage());
      $this->event_dispatcher->dispatch(new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped()) {
      $this->logger->error('UploadError -> Propagation stopped');

      return null;
    }

    /** @var Program|null $old_program */
    $old_program = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if (null !== $old_program) {
      $program = $old_program;
      $this->removeAllTags($program);
      // it's an update
      $program->incrementVersion();
      $program->setVisible($old_program->getVisible()); // necessary to keep reported projects invisible after re-upload!
    } else {
      $program = new Program();
      $program->setRemixRoot(true);
      $program->setVisible(true);
    }

    $program->setName($extracted_file->getName());
    $program->setDescription($extracted_file->getDescription());
    $program->setCredits($extracted_file->getNotesAndCredits());
    $program->setUser($request->getUser());
    $program->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $program->setLanguageVersion($extracted_file->getLanguageVersion());
    $program->setUploadIp($request->getIp());
    $program->setFilesize($file->getSize());
    $program->setApproved(false);
    $program->setUploadLanguage('en');
    $program->setUploadedAt(TimeUtils::getDateTime());
    $program->setRemixMigratedAt(null);
    $program->setFlavor($request->getFlavor());
    $program->setDebugBuild($extracted_file->isDebugBuild());
    $this->addTags($program, $extracted_file);

    $this->event_dispatcher->dispatch(new ProgramBeforePersistEvent($extracted_file, $program));

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    // Extensions are added via the ProgramExtensionListener!

    try {
      if (null !== $extracted_file->getScreenshotPath()) {
        $this->screenshot_repository->saveProgramAssetsTemp($extracted_file->getScreenshotPath(), $program->getId());
      }
    } catch (Exception $e) {
      $this->logger->error('UploadError -> saveProgramAssetsTemp failed!', ['exception' => $e->getMessage()]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try {
        $this->screenshot_repository->deleteTempFilesForProgram($program_id);
      } catch (IOException $error) {
        $this->logger->error('UploadError -> deleteTempFilesForProgram failed!', ['exception' => $error]);
        throw $error;
      }

      return null;
    }

    try {
      if (null !== $extracted_file->getScreenshotPath()) {
        $this->screenshot_repository->makeTempProgramAssetsPerm($program->getId());
      }
    } catch (Exception $e) {
      $this->logger->error('UploadError -> makeTempProgramPerm failed!', ['exception' => $e]);
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try {
        $this->screenshot_repository->deletePermProgramAssets($program_id);
      } catch (IOException $error) {
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
    $this->file_repository->saveProjectZipFile($file, $program->getId());

    $this->event_dispatcher->dispatch(new ProgramAfterInsertEvent($extracted_file, $program));
    $this->notifyFollower($program);
    $compressed_file_directory = $this->file_extractor->getExtractDir().'/'.$program->getId();
    if (is_dir($compressed_file_directory)) {
      (new Filesystem())->remove($compressed_file_directory);
    }
    if (is_dir($extracted_file->getPath())) {
      (new Filesystem())->rename($extracted_file->getPath(), $this->file_extractor->getExtractDir().'/'.$program->getId());
    }
    (new Filesystem())->remove($extracted_file->getPath());

    // remove old "cached" zips - they will be re-generated on a project download
    if (!$this->file_repository->checkIfProjectZipFileExists($program->getId())) {
      $this->file_repository->deleteProjectZipFile($program->getId());
    }

    return $program;
  }

  /**
   * Adds a new program from a scratch_program. Doesn't add the Project file.
   *
   * @throws Exception
   */
  public function createProgramFromScratch(?Program $program, User $user, array $program_data): Program
  {
    $modified_time = TimeUtils::dateTimeFromScratch($program_data['history']['modified']);
    if (null === $program) {
      $program = new Program();
      $program->setUser($user);
      $program->setScratchId($program_data['id']);
      $program->setDebugBuild(false);
    } else {
      //throw new Exception($program->getLastModifiedAt()->format('Y-m-d H:i:s'));
      if ($program->getLastModifiedAt()->getTimestamp() > $modified_time->getTimestamp()) {
        return $program;
      }
      $program->incrementVersion();
    }
    $program->setVisible(true);
    $program->setApproved(false);

    $description_text = '';
    if ($instructions = $program_data['instructions'] ?? null) {
      $description_text .= $instructions;
    }
    if ($description = $program_data['description'] ?? null) {
      if ($instructions) {
        $description_text .= "\n\n";
      }
      $description_text .= $description;
    }
    $program->setDescription($description_text);

    if ($title = $program_data['title'] ?? null) {
      $program->setName($title);
    }

    $shared_time = TimeUtils::dateTimeFromScratch($program_data['history']['shared']);
    if ($shared_time) {
      $program->setUploadedAt($shared_time);
    } else {
      $program->setUploadedAt(TimeUtils::getDateTime());
    }
    if ($modified_time) {
      $program->setLastModifiedAt($modified_time);
    } else {
      $program->setLastModifiedAt(TimeUtils::getDateTime());
    }

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->notifyFollower($program);

    if ($image_url = $program_data['image'] ?? null) {
      $this->screenshot_repository->saveScratchScreenshot($program->getScratchId(), $program->getId());
    }

    return $program;
  }

  /**
   * @return ProgramLike[]
   */
  public function findUserLikes(string $project_id, string $user_id): array
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
    if (ProgramLike::ACTION_ADD === $action) {
      $this->program_like_repository->addLike($project, $user, $type);
    } elseif (ProgramLike::ACTION_REMOVE === $action) {
      $this->program_like_repository->removeLike($project, $user, $type);
    } else {
      throw new InvalidArgumentException("Invalid action: {$action}");
    }
  }

  /**
   * @throws NoResultException
   */
  public function areThereOtherLikeTypes(Program $project, User $user, int $type): bool
  {
    try {
      return $this->program_like_repository->areThereOtherLikeTypes($project, $user, $type);
    } catch (NonUniqueResultException $exception) {
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

  public function addTags(Program $program, ExtractedCatrobatFile $extracted_file): void
  {
    $tags = $extracted_file->getTags();

    if (!empty($tags)) {
      $i = 0;
      foreach ($tags as $tag) {
        /** @var Tag|null $db_tag */
        $db_tag = $this->tag_repository->findOneBy(['internal_title' => $tag]);

        if (null !== $db_tag) {
          $program->addTag($db_tag);
          ++$i;
        }

        if (3 === $i) {
          // Only 3 tags at once!
          break;
        }
      }
    }
  }

  public function removeAllTags(Program $program): void
  {
    $tags = $program->getTags();

    foreach ($tags as $tag) {
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

  public function findProjectIfVisibleToCurrentUser(string $id): ?Program
  {
    /** @var Program|null $project */
    $project = $this->find($id);

    if (null === $project) {
      $this->logger->warning("Project with `{$id}` can't be found.");
    } elseif ($this->isProjectVisibleForCurrentUser($project)) {
      return $project;
    }

    return null;
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

  public function getUserProjects(string $user_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->program_repository->getUserProjectsIncludingPrivateOnes($user_id, $flavor, $max_version, $limit, $offset);
  }

  public function countUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    return $this->program_repository->countUserProjectsIncludingPrivateOnes($user_id, $flavor, $max_version);
  }

  public function getMoreProjectsFromUser(string $user_id, string $project_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->program_repository->getMoreProjectsFromUser($user_id, $project_id, $flavor, $max_version, $limit, $offset);
  }

  public function getPublicUserProjects(string $user_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->program_repository->getPublicUserProjects($user_id, $flavor, $max_version, $limit, $offset);
  }

  public function countPublicUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    return $this->program_repository->countPublicUserProjects($user_id, $flavor, $max_version);
  }

  public function getRecentPrograms(string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->program_repository->getProjects($flavor, $max_version, $limit, $offset, 'uploaded_at');
  }

  public function getMostViewedPrograms(string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->program_repository->getProjects($flavor, $max_version, $limit, $offset, 'views');
  }

  public function getExamplePrograms(?string $flavor = null, ?int $limit = null, int $offset = 0, string $max_version = ''): array
  {
    return $this->example_repository->getExamplePrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getExampleProgramsCount(?string $flavor = null, string $max_version = ''): int
  {
    return $this->example_repository->getExampleProgramsCount(
      $this->app_request->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getScratchRemixesPrograms(string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->program_repository->getScratchRemixProjects($flavor, $max_version, $limit, $offset);
  }

  public function getScratchRemixesProgramsCount(string $flavor = null, string $max_version = ''): int
  {
    return $this->program_repository->countScratchRemixProjects($flavor, $max_version);
  }

  public function getMostDownloadedPrograms(string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->program_repository->getProjects($flavor, $max_version, $limit, $offset, 'downloads');
  }

  public function getRandomPrograms(string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->program_repository->getProjects($flavor, $max_version, $limit, $offset, 'rand');
  }

  public function countProjects(string $flavor = null, string $max_version = ''): int
  {
    return $this->program_repository->countProjects($flavor, $max_version);
  }

  public function search(string $query, ?int $limit = 20, int $offset = 0, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): array
  {
    $program_query = $this->programSearchQuery($query, $max_version, $flavor, $is_debug_request);

    return $this->program_finder->find($program_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): int
  {
    $program_query = $this->programSearchQuery($query, $max_version, $flavor, $is_debug_request);

    $paginator = $this->program_finder->findPaginated($program_query);

    return $paginator->getNbResults();
  }

  public function increaseViews(Program $program): void
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  public function increaseDownloads(Program $program, ?User $user): void
  {
    $this->increaseNumberOfDownloads($program, $user, ProgramDownloads::TYPE_PROJECT);
    $this->addDownloadEntry($program, $user, ProgramDownloads::TYPE_PROJECT);
  }

  public function increaseApkDownloads(Program $program, ?User $user): void
  {
    $this->increaseNumberOfDownloads($program, $user, ProgramDownloads::TYPE_APK);
    $this->addDownloadEntry($program, $user, ProgramDownloads::TYPE_APK);
  }

  protected function increaseNumberOfDownloads(Program $program, ?User $user, string $download_type): void
  {
    if (!is_null($user)) {
      $download_repo = $this->entity_manager->getRepository(ProgramDownloads::class);
      // No matter which type it should only count once!
      $download = $download_repo->findOneBy(['program' => $program, 'user' => $user]);

      // the simplified DQL is the only solution that guarantees proper count: https://stackoverflow.com/questions/24681613/doctrine-entity-increase-value-download-counter
      if (is_null($download)) {
        if (ProgramDownloads::TYPE_PROJECT === $download_type) {
          $this->entity_manager->createQuery('UPDATE App\Entity\Program p SET p.downloads = p.downloads + 1')->execute();
        } elseif (ProgramDownloads::TYPE_APK === $download_type) {
          $this->entity_manager->createQuery('UPDATE App\Entity\Program p SET p.apk_downloads = p.apk_downloads + 1')->execute();
        }
      }
    }
  }

  protected function addDownloadEntry(Program $program, ?User $user, string $download_type): void
  {
    $download = new ProgramDownloads();
    $download->setUser($user);
    $download->setProgram($program);
    $download->setType($download_type);
    $download->setDownloadedAt(new DateTime('now'));
    $this->entity_manager->persist($download);
    $this->entity_manager->flush();
  }

  public function save(Program $program, ProgramDownloads $downloads = null): void
  {
    $this->entity_manager->persist($program);
    if (!is_null($downloads)) {
      $this->entity_manager->persist($downloads);
    }
    $this->entity_manager->flush();
  }

  public function getProgramsByTagInternalTitle(string $name, ?int $limit, int $offset): array
  {
    return $this->program_repository->getProjectsByTagInternalTitle($name, $limit, $offset);
  }

  public function getProjectsByExtensionInternalTitle(string $name, ?int $limit, int $offset): array
  {
    return $this->program_repository->getProjectsByExtensionInternalTitle($name, $limit, $offset);
  }

  public function searchTagCount(string $tag_name): int
  {
    return $this->program_repository->searchTagCount($tag_name);
  }

  public function searchExtensionCount(string $query): int
  {
    return $this->program_repository->searchExtensionCount($query);
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(string $flavor, Program $program, ?int $limit, int $offset): array
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $flavor, $program, $limit, $offset
    );
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

  public function getProjects(string $category, string $max_version = '',
                              int $limit = 20, int $offset = 0, string $flavor = null): array
  {
    switch ($category) {
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

  public function getProjectsCount(string $category, string $max_version = '', string $flavor = null): int
  {
    switch ($category) {
      case 'recent':
      case 'random':
      case 'most_viewed':
      case 'most_downloaded':
        return $this->countProjects($flavor, $max_version);
      case 'example':
        return $this->getExampleProgramsCount($flavor, $max_version);
      case 'scratch':
        return $this->getScratchRemixesProgramsCount($flavor, $max_version);
      default:
        return 0;
    }
  }

  private function programSearchQuery(string $query, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word) {
      $word = $word.'*';
    }
    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'name', 'description', 'getUsernameString', 'getTagsString', 'getExtensionsString']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();

    $bool_query->addMust(new Terms('private', [false]));
    $bool_query->addMust(new Terms('visible', [true]));

    if (!$is_debug_request) {
      $bool_query->addMust(new Terms('debug_build', [false]));
    }

    if ('' !== $max_version) {
      $bool_query->addMust(new Range('language_version', ['lte' => $max_version]));
    }
    if (null !== $flavor && '' !== trim($flavor)) {
      $bool_query->addMust(new Terms('flavor', [$flavor]));
    }

    $bool_query->addMust($query_string);

    return $bool_query;
  }

  private function notifyFollower(Program $program): void
  {
    $followers = $program->getUser()->getFollowers();
    for ($i = 0; $i < $followers->count(); ++$i) {
      $notification = new NewProgramNotification($followers[$i], $program);
      $this->notification_service->addNotification($notification);
    }
  }
}
