<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiLoader;
use App\Catrobat\RecommenderSystem\RecommenderManager;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Repository\FeaturedRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProjectsApiLoader extends AbstractApiLoader
{
  private ProgramManager $project_manager;
  private RecommenderManager $recommender_manager;
  private FeaturedRepository $featured_repository;
  private RequestStack $request_stack;

  public function __construct(
    ProgramManager $project_manager,
    RecommenderManager $recommender_manager,
    FeaturedRepository $featured_repository,
    RequestStack $request_stack
  ) {
    $this->project_manager = $project_manager;
    $this->recommender_manager = $recommender_manager;
    $this->featured_repository = $featured_repository;
    $this->request_stack = $request_stack;
  }

  public function findProjectsByID(string $id, bool $include_private = false): array
  {
    return $this->project_manager->getProgram($id, $include_private);
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

  public function getProjectsFromCategory(string $category, string $max_version, int $limit, int $offset, string $flavor, ?User $user = null): array
  {
    if ('recommended' === $category) {
      return $this->recommender_manager->getProjects($user, $limit, $offset, $flavor, $max_version);
    }

    return $this->project_manager->getProjects($category, $max_version, $limit, $offset, $flavor);
  }

  public function getFeaturedProjects(?string $flavor, int $limit, int $offset, string $platform, string $max_version)
  {
    return $this->featured_repository->getFeaturedPrograms($flavor, $limit, $offset, $platform, $max_version);
  }

  public function getRecommendedProjects(string $project_id, string $category, string $max_version, int $limit, int $offset, string $flavor, ?User $user): array
  {
    $project = $this->findProjectByID($project_id, true);

    switch ($category) {
      case 'similar':
        return $this->project_manager->getRecommendedProgramsById($project_id, $flavor, $limit, $offset);

      case 'also_downloaded':
        return $this->project_manager->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $project, $limit, $offset);

      case 'more_from_user':
        /** @var Program $project */
        $project = $project->isExample() ? $project->getProgram() : $project;
        $project_user_id = $project->getUser()->getId();
        if (null !== $user && $user->getId() === $project_user_id) {
          $projects = $this->project_manager->getUserPrograms($project_user_id, false, $max_version, $limit, $offset, [$project->getId()]);
        } else {
          $projects = $this->project_manager->getPublicUserPrograms($project_user_id, false, $max_version, $limit, $offset, [$project->getId()]);
        }

        return array_filter($projects, function ($program) use ($project) {
          return $program->getId() !== $project->getId();
        });
    }

    return [];
  }

  public function getUserProjects(string $username, int $limit, int $offset, string $flavor, string $max_version): array
  {
    return $this->project_manager->getUserProjects($username, $limit, $offset, $flavor, $max_version);
  }

  public function getUserPublicPrograms(string $user_id, int $limit, int $offset, string $flavor, string $max_version): array
  {
    return $this->project_manager->getUserPublicPrograms($user_id, $limit, $offset, $flavor, $max_version);
  }

  public function getClientIp(): ?string
  {
    return $this->request_stack->getCurrentRequest()->getClientIp();
  }
}
