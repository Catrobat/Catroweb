<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramDownloads;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\Project\Special\ExampleRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\CatrobatFileSanitizer;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\Event\ProjectAfterInsertEvent;
use App\Project\Event\ProjectBeforeInsertEvent;
use App\Project\Event\ProjectBeforePersistEvent;
use App\Storage\ScreenshotRepository;
use App\User\Notification\NotificationManager;
use App\Utils\RequestHelper;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectManager
{
  public function __construct(
    protected CatrobatFileExtractor $file_extractor,
    protected ProjectFileRepository $file_repository,
    protected ScreenshotRepository $screenshot_repository,
    protected EntityManagerInterface $entity_manager,
    protected ProgramRepository $project_repository,
    protected TagRepository $tag_repository,
    protected FeaturedRepository $featured_repository,
    protected ExampleRepository $example_repository,
    protected EventDispatcherInterface $event_dispatcher,
    private readonly LoggerInterface $logger,
    protected RequestHelper $request_helper,
    protected ExtensionRepository $extension_repository,
    protected CatrobatFileSanitizer $file_sanitizer,
    protected NotificationManager $notification_service,
    private readonly ?UrlHelper $urlHelper,
    protected Security $security,
  ) {
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
    return $this->project_repository->getProjectByID($id, $include_private);
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

    if (!$project->isVisible() && !$this->featured_repository->isFeatured($project)) {
      return false;
    }

    // Auto-hidden projects (community moderation) are visible to the owner and admins
    if ($project->getAutoHidden()) {
      if (null === $user) {
        return false;
      }
      if ($project->getUser() === $user) {
        return true;
      }

      return $this->security->isGranted('ROLE_ADMIN');
    }

    // SHARE-49: Private projects are visible to everyone.
    // -
    // SHARE-70/SHARE-296: Debug projects must only be seen in the dev env or if explicitly requested
    if (!$project->isDebugBuild()) {
      return true;
    }
    if ($this->request_helper->isDebugBuildRequest()) {
      return true;
    }

    return 'dev' === $_ENV['APP_ENV'];
  }

  /*
   * Adds a new project and notifies all followers of the uploader about it.
   *
   * @throws Exception
   */
  /**
   * @throws ORMException
   */
  public function addProject(AddProjectRequest $request): ?Program
  {
    $file = $request->getProjectFile();

    $extracted_file = $this->file_extractor->extract($file);

    $this->file_sanitizer->sanitize($extracted_file);

    try {
      $event = $this->event_dispatcher->dispatch(new ProjectBeforeInsertEvent($extracted_file));
    } catch (InvalidCatrobatFileException $invalidCatrobatFileException) {
      $this->logger->error('addProject failed with code: '.$invalidCatrobatFileException->getCode().' and message:'.$invalidCatrobatFileException->getMessage());
      throw $invalidCatrobatFileException;
    }

    if ($event->isPropagationStopped()) {
      $this->logger->error('UploadError -> Propagation stopped');

      return null;
    }

    /** @var Program|null $old_project */
    $old_project = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if (null !== $old_project) {
      $project = $old_project;
      $this->removeAllTags($project);
      // it's an update
      $project->incrementVersion();
      $project->setVisible($old_project->getVisible()); // necessary to keep reported projects invisible after re-upload!
    } else {
      $project = new Program();
      $project->setRemixRoot(true);
      $project->setVisible(true);
    }

    $project->setName($extracted_file->getName());
    $project->setDescription($extracted_file->getDescription());
    $project->setCredits($extracted_file->getNotesAndCredits());
    $project->setUser($request->getUser());
    $project->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $project->setLanguageVersion($extracted_file->getLanguageVersion());
    $project->setUploadIp($request->getIp());
    $project->setFilesize($file->getSize());
    $project->setApproved(false);
    $project->setUploadLanguage('en');
    $project->setUploadedAt(TimeUtils::getDateTime());
    $project->setRemixMigratedAt(null);
    $project->setFlavor($request->getFlavor());
    $project->setDebugBuild($extracted_file->isDebugBuild());
    $this->addTags($project, $extracted_file);

    $this->event_dispatcher->dispatch(new ProjectBeforePersistEvent($extracted_file, $project));

    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($project);

    // Extensions are added via the ProjectExtensionListener!

    try {
      if (null !== $extracted_file->getScreenshotPath()) {
        $this->screenshot_repository->saveProjectAssetsTemp($extracted_file->getScreenshotPath(), $project->getId());
      }
    } catch (\Exception $exception) {
      $this->logger->error('UploadError -> saveProjectAssetsTemp failed!', ['exception' => $exception->getMessage()]);
      $project_id = $project->getId();
      $this->entity_manager->remove($project);
      $this->entity_manager->flush();
      try {
        $this->screenshot_repository->deleteTempFilesForProject($project_id);
      } catch (IOException $error) {
        $this->logger->error('UploadError -> deleteTempFilesForProject failed!', ['exception' => $error]);
        throw $error;
      }

      return null;
    }

    try {
      if (null !== $extracted_file->getScreenshotPath()) {
        $this->screenshot_repository->makeTempProjectAssetsPerm($project->getId());
      }
    } catch (\Exception $exception) {
      $this->logger->error('UploadError -> makeTempProjectPerm failed!', ['exception' => $exception]);
      $project_id = $project->getId();
      $this->entity_manager->remove($project);
      $this->entity_manager->flush();
      try {
        $this->screenshot_repository->deletePermProjectAssets($project_id);
      } catch (IOException $error) {
        $this->logger->error(
          'UploadError -> deletePermProjectAssets or deleteProjectFile failed!', ['exception' => $error]
        );
        throw $error;
      }

      return null;
    }

    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($project);

    $this->file_repository->saveProjectZipFile($file, $project->getId());

    $this->event_dispatcher->dispatch(new ProjectAfterInsertEvent($extracted_file, $project));
    $this->notifyFollower($project);
    $compressed_file_directory = $this->file_extractor->getExtractDir().'/'.$project->getId();
    if (is_dir($compressed_file_directory)) {
      new Filesystem()->remove($compressed_file_directory);
    }

    if (is_dir($extracted_file->getPath())) {
      new Filesystem()->rename($extracted_file->getPath(), $this->file_extractor->getExtractDir().'/'.$project->getId());
    }

    new Filesystem()->remove($extracted_file->getPath());

    // remove old "cached" zips - they will be re-generated on a project download
    if (!$this->file_repository->checkIfProjectZipFileExists($project->getId())) {
      $this->file_repository->deleteProjectZipFile($project->getId());
    }

    return $project;
  }

  /**
   * Adds a new project from a scratch_project. Doesn't add the Project file.
   *
   * @throws ORMException
   * @throws \ImagickException
   */
  public function createProjectFromScratch(?Program $project, User $user, array $project_data): Program
  {
    $modified_time = TimeUtils::dateTimeFromScratch($project_data['history']['modified']);
    if (!$project instanceof Program) {
      $project = new Program();
      $project->setUser($user);
      $project->setScratchId($project_data['id']);
      $project->setDebugBuild(false);
    } else {
      // throw new Exception($project->getLastModifiedAt()->format('Y-m-d H:i:s'));
      if ($project->getLastModifiedAt()->getTimestamp() > $modified_time->getTimestamp()) {
        return $project;
      }

      $project->incrementVersion();
    }

    $project->setVisible(true);
    $project->setApproved(false);

    $description_text = '';
    if ($instructions = $project_data['instructions'] ?? null) {
      $description_text .= $instructions;
    }

    if ($description = $project_data['description'] ?? null) {
      if ($instructions) {
        $description_text .= "\n\n";
      }

      $description_text .= $description;
    }

    $project->setDescription($description_text);

    if ($title = $project_data['title'] ?? null) {
      $project->setName($title);
    }

    $shared_time = TimeUtils::dateTimeFromScratch($project_data['history']['shared']);
    if ($shared_time instanceof \DateTime) {
      $project->setUploadedAt($shared_time);
    } else {
      $project->setUploadedAt(TimeUtils::getDateTime());
    }

    if ($modified_time instanceof \DateTime) {
      $project->setLastModifiedAt($modified_time);
    } else {
      $project->setLastModifiedAt(TimeUtils::getDateTime());
    }

    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($project);

    $this->notifyFollower($project);

    if ($project_data['image'] ?? false) {
      $this->screenshot_repository->saveScratchScreenshot($project->getScratchId(), $project->getId());
    }

    return $project;
  }

  public function addTags(Program $project, ExtractedCatrobatFile $extracted_file): void
  {
    $tags = $extracted_file->getTags();

    if ([] !== $tags) {
      $i = 0;
      foreach ($tags as $tag) {
        /** @var Tag|null $db_tag */
        $db_tag = $this->tag_repository->findOneBy(['internal_title' => $tag]);

        if (null !== $db_tag) {
          $project->addTag($db_tag);
          ++$i;
        }

        if (3 === $i) {
          // Only 3 tags at once!
          break;
        }
      }
    }
  }

  public function removeAllTags(Program $project): void
  {
    $tags = $project->getTags();

    foreach ($tags as $tag) {
      $project->removeTag($tag);
    }
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function markAllProjectsAsNotYetMigrated(): void
  {
    $this->project_repository->markAllProjectsAsNotYetMigrated();
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByNameAndUser(string $project_name, UserInterface $user): ?Program
  {
    return $this->project_repository->findOneBy([
      'name' => $project_name,
      'user' => $user,
    ]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByName(string $project_name): ?Program
  {
    return $this->project_repository->findOneBy(['name' => $project_name]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByScratchId(int $scratch_id): ?Program
  {
    return $this->project_repository->findOneBy(['scratch_id' => $scratch_id]);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
  {
    return $this->project_repository->findBy($criteria, $orderBy, $limit, $offset);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findAll(): array
  {
    return $this->project_repository->findAll();
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   *
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findNext(string $previous_project_id): mixed
  {
    return $this->project_repository->findNext($previous_project_id);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function find(string $id): ?Program
  {
    return $this->project_repository->find($id);
  }

  public function findProjectIfVisibleToCurrentUser(?string $id): ?Program
  {
    if (null === $id) {
      return null;
    }

    /** @var Program|null $project */
    $project = $this->find($id);

    if (null === $project) {
      $this->logger->warning(sprintf('Project with `%s` can\'t be found.', $id));
    } elseif ($this->isProjectVisibleForCurrentUser($project)) {
      return $project;
    }

    return null;
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findOneByRemixMigratedAt(?\DateTime $remix_migrated_at): ?Program
  {
    return $this->project_repository->findOneBy(['remix_migrated_at' => $remix_migrated_at]);
  }

  public function getUserProjects(string $user_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->project_repository->getUserProjectsIncludingPrivateOnes($user_id, $flavor, $max_version, $limit, $offset);
  }

  public function countUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    return $this->project_repository->countUserProjectsIncludingPrivateOnes($user_id, $flavor, $max_version);
  }

  public function getMoreProjectsFromUser(string $user_id, string $project_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->project_repository->getMoreProjectsFromUser($user_id, $project_id, $flavor, $max_version, $limit, $offset);
  }

  public function getPublicUserProjects(string $user_id, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    return $this->project_repository->getPublicUserProjects($user_id, $flavor, $max_version, $limit, $offset);
  }

  public function countPublicUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    return $this->project_repository->countPublicUserProjects($user_id, $flavor, $max_version);
  }

  public function getRecentProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getProjects($flavor, $max_version, $limit, $offset, 'uploaded_at');
  }

  public function getMostViewedProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getProjects($flavor, $max_version, $limit, $offset, 'views');
  }

  public function getExampleProjects(?string $flavor = null, ?int $limit = null, int $offset = 0, string $max_version = ''): array
  {
    return $this->example_repository->getExampleProjects(
      $this->request_helper->isDebugBuildRequest(), $flavor, $limit, $offset, $max_version
    );
  }

  public function getExampleProjectsCount(?string $flavor = null, string $max_version = ''): int
  {
    return $this->example_repository->getExampleProjectsCount(
      $this->request_helper->isDebugBuildRequest(), $flavor, $max_version
    );
  }

  public function getScratchRemixesProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getScratchRemixProjects($flavor, $max_version, $limit, $offset);
  }

  public function getScratchRemixesProjectsCount(?string $flavor = null, string $max_version = ''): int
  {
    return $this->project_repository->countScratchRemixProjects($flavor, $max_version);
  }

  public function getMostDownloadedProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getProjects($flavor, $max_version, $limit, $offset, 'downloads');
  }

  private function getTrendingProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getTrendingProjects($flavor, $max_version, $limit, $offset, 'downloads');
  }

  public function getRandomProjects(?string $flavor = null, int $limit = 20, int $offset = 0, string $max_version = ''): array
  {
    return $this->project_repository->getProjects($flavor, $max_version, $limit, $offset, 'rand');
  }

  public function countProjects(?string $flavor = null, string $max_version = ''): int
  {
    return $this->project_repository->countProjects($flavor, $max_version);
  }

  public function save(Program $project, ?ProgramDownloads $downloads = null): void
  {
    $this->entity_manager->persist($project);
    if (!is_null($downloads)) {
      $this->entity_manager->persist($downloads);
    }

    $this->entity_manager->flush();
  }

  public function getProjectsByTagInternalTitle(string $name, ?int $limit, int $offset): array
  {
    return $this->project_repository->getProjectsByTagInternalTitle($name, $limit, $offset);
  }

  public function getProjectsByExtensionInternalTitle(string $name, ?int $limit, int $offset): array
  {
    return $this->project_repository->getProjectsByExtensionInternalTitle($name, $limit, $offset);
  }

  public function searchTagCount(string $tag_name): int
  {
    return $this->project_repository->searchTagCount($tag_name);
  }

  public function searchExtensionCount(string $query): int
  {
    return $this->project_repository->searchExtensionCount($query);
  }

  public function getOtherMostDownloadedProjectsOfUsersThatAlsoDownloadedGivenProject(string $flavor, Program $project, ?int $limit, int $offset): array
  {
    return $this->project_repository->getOtherMostDownloadedProjectsOfUsersThatAlsoDownloadedGivenProject(
      $flavor, $project, $limit, $offset
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

  /**
   * @throws \JsonException
   */
  public function decodeToken(string $token): array
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1], true);

    return json_decode($tokenPayload, true, 512, JSON_THROW_ON_ERROR);
  }

  public function getProjects(string $category, string $max_version = '',
    int $limit = 20, int $offset = 0, ?string $flavor = null): array
  {
    return match ($category) {
      'recent' => $this->getRecentProjects($flavor, $limit, $offset, $max_version),
      'random' => $this->getRandomProjects($flavor, $limit, $offset, $max_version),
      'most_viewed' => $this->getMostViewedProjects($flavor, $limit, $offset, $max_version),
      'most_downloaded' => $this->getMostDownloadedProjects($flavor, $limit, $offset, $max_version),
      'example' => $this->getExampleProjects($flavor, $limit, $offset, $max_version),
      'scratch' => $this->getScratchRemixesProjects($flavor, $limit, $offset, $max_version),
      'popular' => $this->getPopularProjects($flavor, $limit, $offset, $max_version),
      'trending' => $this->getTrendingProjects($flavor, $limit, $offset, $max_version),
      default => [],
    };
  }

  public function getProjectsCount(string $category, string $max_version = '', ?string $flavor = null): int
  {
    return match ($category) {
      'recent', 'random', 'most_viewed', 'most_downloaded', 'trending' => $this->countProjects($flavor, $max_version),
      'example' => $this->getExampleProjectsCount($flavor, $max_version),
      'scratch' => $this->getScratchRemixesProjectsCount($flavor, $max_version),
      default => 0,
    };
  }

  private function notifyFollower(Program $project): void
  {
    $followers = $project->getUser()->getFollowers();
    for ($i = 0; $i < $followers->count(); ++$i) {
      $notification = new NewProgramNotification($followers[$i], $project);
      $this->notification_service->addNotification($notification);
    }
  }

  /**
   * @throws ORMException
   */
  public function deleteProject(Program $project): void
  {
    $project->setVisible(false);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($project);
  }

  private function getPopularProjects(?string $flavor, int $limit, int $offset, string $max_version): array
  {
    return $this->project_repository->getProjects($flavor, $max_version, $limit, $offset, 'popularity');
  }
}
