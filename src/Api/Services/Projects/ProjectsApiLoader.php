<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProjectCodeStatistics;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\CatrobatFile\ProjectZipReconstructor;
use App\Project\CodeStatistics\CodeStatisticsService;
use App\Project\ProjectManager;
use App\Project\ProjectSearchService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectsApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ProjectSearchService $project_search_service,
    private readonly FeaturedRepository $featured_repository,
    private readonly TagRepository $tag_repository,
    private readonly ExtensionRepository $extension_repository,
    private readonly RequestStack $request_stack,
    private readonly CodeStatisticsService $code_statistics_service,
    protected ProjectFileRepository $file_repository,
    protected ExtractedFileRepository $extracted_file_repository,
    protected LoggerInterface $logger,
    private readonly Security $security,
    private readonly ProjectZipReconstructor $zip_reconstructor,
  ) {
  }

  /**
   * @param array<Program> $projects
   *
   * @return array<Program>
   */
  private function filterNotSafeForMinors(array $projects): array
  {
    $user = $this->security->getUser();
    if (!$user instanceof User || !$user->isMinor()) {
      return $projects;
    }

    return array_values(array_filter($projects, static fn (Program $p): bool => 0 === $p->getNotForKids()));
  }

  public function findProjectsByID(string $id, bool $include_private = false): array
  {
    return $this->project_manager->getProjectByID($id, $include_private);
  }

  public function findProjectByID(string $id, bool $include_private = false): ?Program
  {
    $projects = $this->findProjectsByID($id, $include_private);

    return [] === $projects ? null : $projects[0];
  }

  public function searchProjects(string $query, int $limit, int $offset, string $max_version, string $flavor): array
  {
    if ('' === trim($query) || ctype_space($query)) {
      return [];
    }

    return $this->filterNotSafeForMinors($this->project_search_service->search($query, $limit, $offset, $max_version, $flavor));
  }

  public function getProjectsFromCategory(string $category, string $max_version, int $limit, int $offset, string $flavor, ?User $user = null): array
  {
    if ('recommended' === $category) {
      return []; // Feature removed
    }

    return $this->filterNotSafeForMinors($this->project_manager->getProjects($category, $max_version, $limit, $offset, $flavor));
  }

  public function getFeaturedProjects(?string $flavor, int $limit, int $offset, string $platform, string $max_version): mixed
  {
    return $this->featured_repository->getFeaturedPrograms($flavor, $limit, $offset, $platform, $max_version);
  }

  public function getRecommendedProjects(string $project_id, string $category, string $max_version, int $limit, int $offset, string $flavor, ?User $user): array
  {
    $project = $this->findProjectByID($project_id, true);

    switch ($category) {
      case 'similar':
      case 'also_downloaded':
        return []; // Features removed

      case 'more_from_user':
        /** @var Program $project */
        $project = $project->isExample() ? $project->getProgram() : $project;
        $project_user_id = $project->getUser()->getId();

        return $this->filterNotSafeForMinors($this->project_manager->getMoreProjectsFromUser($project_user_id, $project_id, $limit, $offset, $flavor, $max_version));
    }

    return [];
  }

  public function getUserProjects(string $user_id, int $limit, int $offset, string $flavor, string $max_version): array
  {
    return $this->filterNotSafeForMinors($this->project_manager->getUserProjects($user_id, $limit, $offset, $flavor, $max_version));
  }

  public function getUserPublicProjects(string $user_id, int $limit, int $offset, string $flavor, string $max_version): array
  {
    return $this->project_manager->getPublicUserProjects($user_id, $limit, $offset, $flavor, $max_version);
  }

  public function getClientIp(): ?string
  {
    return $this->request_stack->getCurrentRequest()->getClientIp();
  }

  public function getProjectExtensions(): array
  {
    return $this->extension_repository->getActiveExtensions();
  }

  public function getProjectTags(): array
  {
    return $this->tag_repository->getActiveTags();
  }

  public function getProjectCatrobatZipFile(string $id): ?File
  {
    try {
      if (!$this->file_repository->checkIfProjectZipFileExists($id)) {
        // Try reconstructing from content-addressable store first
        $reconstructed = $this->zip_reconstructor->reconstruct($id);
        if (null === $reconstructed) {
          // Fall back to existing behavior: zip from extracted files
          $this->file_repository->zipProject($this->extracted_file_repository->getBaseDir($id), $id);
        }
      }

      $zipFile = $this->file_repository->getProjectZipFile($id);
      if (!$zipFile->isFile()) {
        return null;
      }
    } catch (FileNotFoundException) {
      return null;
    } catch (\Throwable $e) {
      $this->logger->error(sprintf('Can\'t get project zip for "%s"; error: ', $id).$e->getMessage());

      return null;
    }

    return $zipFile;
  }

  public function getCodeStatistics(Program $project): ?ProjectCodeStatistics
  {
    return $this->code_statistics_service->getStatistics($project);
  }
}
