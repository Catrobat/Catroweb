<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProjectListResponse;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @deprecated
 */
class ListProjectsController extends AbstractController
{
  public function __construct(private readonly ProjectManager $project_manager)
  {
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/recent.json', name: 'api_recent_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listProjects(Request $request): ProjectListResponse
  {
    return $this->listSortedProjects($request, 'recent');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/mostDownloaded.json', name: 'api_most_downloaded_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listMostDownloadedProjects(Request $request): ProjectListResponse
  {
    return $this->listSortedProjects($request, 'downloads');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/mostViewed.json', name: 'api_most_viewed_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listMostViewedProjects(Request $request): ProjectListResponse
  {
    return $this->listSortedProjects($request, 'views');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/randomProjects.json', name: 'api_random_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listRandomProjects(Request $request): ProjectListResponse
  {
    return $this->listSortedProjects($request, 'random');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/userProjects.json', name: 'api_user_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listUserProjects(Request $request): ProjectListResponse
  {
    return $this->listSortedProjects($request, 'user');
  }

  private function listSortedProjects(Request $request, string $sortBy): ProjectListResponse
  {
    $flavor = $request->attributes->get('flavor');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);
    $user_id = (string) $request->query->get('user_id', '0');
    $max_version = (string) $request->query->get('max_version', '');

    if ('downloads' === $sortBy) {
      $projects = $this->project_manager->getMostDownloadedProjects($flavor, $limit, $offset, $max_version);
      $projects = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $projects, $this->project_manager->getMostDownloadedProjects(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('views' === $sortBy) {
      $projects = $this->project_manager->getMostViewedProjects($flavor, $limit, $offset, $max_version);
      $projects = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $projects, $this->project_manager->getMostViewedProjects(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('random' === $sortBy) {
      $projects = $this->project_manager->getRandomProjects($flavor, $limit, $offset, $max_version);
      $projects = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $projects, $this->project_manager->getRandomProjects(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('user' === $sortBy) {
      /** @var User|null $user */
      $user = $this->getUser();
      if (null !== $user && $user->getId() === $user_id) {
        $projects = $this->project_manager->getUserProjects($user_id, null, null, null, $max_version);
      } else {
        $projects = $this->project_manager->getPublicUserProjects($user_id, null, null, null, $max_version);
      }
    } else {
      if ('pocketcode' === $flavor) {
        // For our default flavor we like to provide users with new projects of all flavors in the recent category
        $flavor = null;
      }
      $projects = $this->project_manager->getRecentProjects($flavor, $limit, $offset, $max_version);
    }

    if ('user' === $sortBy || 'example' === $sortBy) {
      $numbOfTotalProjects = count($projects);
    } else {
      $numbOfTotalProjects = $this->project_manager->countProjects(null, $max_version);
    }

    return new ProjectListResponse($projects, $numbOfTotalProjects);
  }

  private function fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
    array $projects, callable $getMoreProjects, string $flavor, int $limit, int $offset, string $max_version): array
  {
    $number_of_projects = count($projects);

    if ($number_of_projects >= $limit || !$flavor) {
      return $projects; // Nothing to do. There are already enough projects or we don't know the already used flavor
    }

    $new_limit = $limit - $number_of_projects;

    $total_number_of_correct_flavored_projects = $this->project_manager->countProjects($flavor, $max_version);
    $new_offset = max($offset - $total_number_of_correct_flavored_projects + $number_of_projects, 0);

    return array_merge($projects, $getMoreProjects('!'.$flavor, $new_limit, $new_offset, $max_version));
  }
}
