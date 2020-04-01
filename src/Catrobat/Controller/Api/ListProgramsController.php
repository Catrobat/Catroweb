<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\ProgramManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListProgramsController extends AbstractController
{
  private ProgramManager $program_manager;

  public function __construct(ProgramManager $program_manager)
  {
    $this->program_manager = $program_manager;
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/recent.json", name="api_recent_programs", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'recent');
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/recentIDs.json", name="api_recent_program_ids", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listProgramIdsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'recent', false);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/mostDownloaded.json", name="api_most_downloaded_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listMostDownloadedProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'downloads');
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/mostDownloadedIDs.json", name="api_most_downloaded_program_ids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listMostDownloadedProgramIdsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'downloads', false);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/mostViewed.json", name="api_most_viewed_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listMostViewedProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'views');
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/mostViewedIDs.json", name="api_most_viewed_programids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listMostViewedProgramIdsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'views', false);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/scratchRemixes.json", name="api_scratch_remix",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listScratchRemixProjectsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'scratchRemix');
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/randomProjects.json", name="api_random_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listRandomProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'random');
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/randomProjectIDs.json", name="api_random_programids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listRandomProgramIdsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'random', false);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/userProjects.json", name="api_user_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function listUserProgramsAction(Request $request): ProgramListResponse
  {
    return $this->listSortedPrograms($request, 'user');
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  private function listSortedPrograms(Request $request, string $sortBy, bool $details = true,
                                      bool $useRequestFlavor = true): ProgramListResponse
  {
    $flavor = null;
    if ($useRequestFlavor)
    {
      $flavor = $request->get('flavor');
    }

    $limit = (int) $request->get('limit', 20);
    $offset = (int) $request->get('offset', 0);
    $user_id = $request->get('user_id', 0);
    $max_version = $request->query->get('max_version', '0');

    if ('downloads' === $sortBy)
    {
      $programs = $this->program_manager->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, [$this->program_manager, 'getMostDownloadedPrograms'],
        $flavor, $limit, $offset, $max_version
      );
    }
    elseif ('views' === $sortBy)
    {
      $programs = $this->program_manager->getMostViewedPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, [$this->program_manager, 'getMostViewedPrograms'],
        $flavor, $limit, $offset, $max_version
      );
    }
    elseif ('scratchRemix' == $sortBy)
    {
      $programs = $this->program_manager->getScratchRemixesPrograms($flavor, $limit, $offset);

      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, [$this->program_manager, 'getScratchRemixesPrograms'],
        $flavor, $limit, $offset, $max_version
      );
    }
    elseif ('random' === $sortBy)
    {
      $programs = $this->program_manager->getRandomPrograms($flavor, $limit, $offset, $max_version);
      $programs = $this->fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(
        $programs, [$this->program_manager, 'getRandomPrograms'],
        $flavor, $limit, $offset, $max_version
      );
    }
    elseif ('user' === $sortBy)
    {
      if (null !== $this->getUser() && $this->getUser()->getId() === $user_id)
      {
        $programs = $this->program_manager->getUserPrograms($user_id, $max_version);
      }
      else
      {
        $programs = $this->program_manager->getPublicUserPrograms($user_id, $max_version);
      }
    }
    else
    {
      if ('pocketcode' === $flavor)
      {
        // For our default flavor we like to provide users with new projects of all flavors in the recent category
        $flavor = null;
      }
      $programs = $this->program_manager->getRecentPrograms($flavor, $limit, $offset, $max_version);
    }

    if ('user' === $sortBy)
    {
      $numbOfTotalProjects = count($programs);
    }
    else
    {
      $numbOfTotalProjects = $this->program_manager->getTotalPrograms(null, $max_version);
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects, $details);
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  private function fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors(array $projects,
                                                                              callable $getMoreProjects,
                                                                              string $flavor,
                                                                              int $limit, int $offset,
                                                                              string $max_version): array
  {
    $number_of_projects = is_countable($projects) ? count($projects) : 0;

    if ($number_of_projects >= $limit || !$flavor)
    {
      return $projects; // Nothing to do. There are already enough projects or we don't know the already used flavor
    }

    $new_limit = $limit - $number_of_projects;

    $total_number_of_correct_flavored_projects = $this->program_manager->getTotalPrograms($flavor, $max_version);
    $new_offset = max($offset - $total_number_of_correct_flavored_projects + $number_of_projects, 0);

    return array_merge($projects, $getMoreProjects('!'.$flavor, $new_limit, $new_offset, $max_version));
  }
}
