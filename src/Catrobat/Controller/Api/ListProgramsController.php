<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\ProgramManager;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListProgramsController.
 */
class ListProgramsController extends AbstractController
{
  /**
   * @var ProgramManager
   */
  private $program_manager;

  /**
   * ListProgramsController constructor.
   */
  public function __construct(ProgramManager $program_manager)
  {
    $this->program_manager = $program_manager;
  }

  /**
   * @Route("/api/projects/recent.json", name="api_recent_programs", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'recent');
  }

  /**
   * @Route("/api/projects/recentIDs.json", name="api_recent_program_ids", defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'recent', false);
  }

  /**
   * @Route("/api/projects/mostDownloaded.json", name="api_most_downloaded_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listMostDownloadedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'downloads');
  }

  /**
   * @Route("/api/projects/mostDownloadedIDs.json", name="api_most_downloaded_program_ids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listMostDownloadedProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'downloads', false);
  }

  /**
   * @Route("/api/projects/mostViewed.json", name="api_most_viewed_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listMostViewedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'views');
  }

  /**
   * @Route("/api/projects/mostViewedIDs.json", name="api_most_viewed_programids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listMostViewedProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'views', false);
  }

  /**
   * @Route("/api/projects/scratchRemixes.json", name="api_scratch_remix",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listScratchRemixProjectsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'scratchRemix');
  }

  /**
   * @Route("/api/projects/randomProjects.json", name="api_random_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listRandomProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'random');
  }

  /**
   * @Route("/api/projects/randomProjectIDs.json", name="api_random_programids",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listRandomProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'random', false);
  }

  /**
   * @Route("/api/projects/userProjects.json", name="api_user_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  public function listUserProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'user');
  }

  /**
   * @param      $sortBy
   * @param bool $details
   * @param bool $useRequestFlavor
   * @param      $flavor
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return ProgramListResponse
   */
  private function listSortedPrograms(Request $request, $sortBy, $details = true, $useRequestFlavor = true)
  {
    $flavor = null;
    if (true === $useRequestFlavor)
    {
      $flavor = $request->get('flavor');
    }

    /**
     * @var GuidType
     */
    $limit = intval($request->get('limit', 20));
    $offset = intval($request->get('offset', 0));
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
   * @param $projects
   * @param $flavor
   * @param $limit
   * @param $offset
   * @param $max_version
   *
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @return array
   */
  private function fillIncompleteFlavoredCategoryProjectsWithDifferentFlavors($projects, callable $getMoreProjects,
                                                                              $flavor, $limit, $offset, $max_version)
  {
    $number_of_projects = count($projects);

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
