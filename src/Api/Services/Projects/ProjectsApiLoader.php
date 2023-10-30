<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProgramFileRepository;
use App\Project\ProgramManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectsApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly ProgramManager $project_manager,
    private readonly FeaturedRepository $featured_repository,
    private readonly TagRepository $tag_repository,
    private readonly ExtensionRepository $extension_repository,
    private readonly RequestStack $request_stack,
    protected ProgramFileRepository $file_repository,
    protected ExtractedFileRepository $extracted_file_repository,
    protected LoggerInterface $logger
  ) {
  }

  public function findProjectsByID(string $id, bool $include_private = false): array
  {
    return $this->project_manager->getProjectByID($id, $include_private);
  }

  public function findProjectByID(string $id, bool $include_private = false): ?Program
  {
    $projects = $this->findProjectsByID($id, $include_private);

    return empty($projects) ? null : $projects[0];
  }

  public function searchProjects(string $query, int $limit, int $offset, string $max_version, string $flavor): array
  {
    if ('' === trim($query) || ctype_space($query)) {
      return [];
    }

    return $this->project_manager->search($query, $limit, $offset, $max_version, $flavor);
  }

  public function getProjectsFromCategory(string $category, string $max_version, int $limit, int $offset, string $flavor, User $user = null): array
  {
    if ('recommended' === $category) {
      return []; // Feature removed
    }

    return $this->project_manager->getProjects($category, $max_version, $limit, $offset, $flavor);
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

        return $this->project_manager->getMoreProjectsFromUser($project_user_id, $project_id, $limit, $offset, $flavor, $max_version);
    }

    return [];
  }

  public function getUserProjects(string $user_id, int $limit, int $offset, string $flavor, string $max_version): array
  {
    return $this->project_manager->getUserProjects($user_id, $limit, $offset, $flavor, $max_version);
  }

  public function getUserPublicPrograms(string $user_id, int $limit, int $offset, string $flavor, string $max_version): array
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
        $this->file_repository->zipProject($this->extracted_file_repository->getBaseDir($id), $id);
      }
      $zipFile = $this->file_repository->getProjectZipFile($id);
      if (!$zipFile->isFile()) {
        return null;
      }
    } catch (FileNotFoundException) {
      return null;
    } catch (\Throwable $e) {
      $this->logger->error("Can't get project zip for \"{$id}\"; error: ".$e->getMessage());

      return null;
    }

    return $zipFile;
  }
}
