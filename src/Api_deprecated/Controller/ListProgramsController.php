<?php

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProgramListResponse;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class ListProgramsController extends AbstractController
{
  public function __construct(private readonly ProjectManager $program_manager)
  {
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/recent.json', name: 'api_recent_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'recent');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/mostDownloaded.json', name: 'api_most_downloaded_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listMostDownloadedProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'downloads');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/mostViewed.json', name: 'api_most_viewed_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listMostViewedProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'views');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/randomProjects.json', name: 'api_random_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listRandomProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'random');
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/userProjects.json', name: 'api_user_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function listUserProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'user');
  }

  private function listSortedPrograms(Request $request, string $sortBy): ProgramListResponse
  {
    $flavor = $request->attributes->get('flavor');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);
    $user_id = (string) $request->query->get('user_id', '0');
    $max_version = (string) $request->query->get('max_version', '');

    if ('downloads' === $sortBy) {
      $programs = $this->program_manager->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, $this->program_manager->getMostDownloadedPrograms(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('views' === $sortBy) {
      $programs = $this->program_manager->getMostViewedPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, $this->program_manager->getMostViewedPrograms(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('random' === $sortBy) {
      $programs = $this->program_manager->getRandomPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, $this->program_manager->getRandomPrograms(...),
        $flavor, $limit, $offset, $max_version
      );
    } elseif ('user' === $sortBy) {
      /** @var User|null $user */
      $user = $this->getUser();
      if (null !== $user && $user->getId() === $user_id) {
        $programs = $this->program_manager->getUserProjects($user_id, null, null, null, $max_version);
      } else {
        $programs = $this->program_manager->getPublicUserProjects($user_id, null, null, null, $max_version);
      }
    } else {
      if ('pocketcode' === $flavor) {
        // For our default flavor we like to provide users with new projects of all flavors in the recent category
        $flavor = null;
      }
      $programs = $this->program_manager->getRecentPrograms($flavor, $limit, $offset, $max_version);
    }

    if ('user' === $sortBy || 'example' === $sortBy) {
      $numbOfTotalProjects = count($programs);
    } else {
      $numbOfTotalProjects = $this->program_manager->countProjects(null, $max_version);
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  private function fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
    array $projects, callable $getMoreProjects, string $flavor, int $limit, int $offset, string $max_version): array
  {
    $number_of_projects = count($projects);

    if ($number_of_projects >= $limit || !$flavor) {
      return $projects; // Nothing to do. There are already enough projects or we don't know the already used flavor
    }

    $new_limit = $limit - $number_of_projects;

    $total_number_of_correct_flavored_projects = $this->program_manager->countProjects($flavor, $max_version);
    $new_offset = max($offset - $total_number_of_correct_flavored_projects + $number_of_projects, 0);

    return array_merge($projects, $getMoreProjects('!'.$flavor, $new_limit, $new_offset, $max_version));
  }
}
