<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Responses\ProgramListResponse;


/**
 * Class ListProgramsController
 * @package App\Catrobat\Controller\Api
 */
class ListProgramsController extends Controller
{

  /**
   * @Route("/api/projects/recent.json", name="api_recent_programs",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   * @param         $flavor
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listProgramsAction(Request $request, $flavor)
  {
    return $this->listSortedPrograms($request, 'recent', true, $flavor);
  }


  /**
   * @Route("/api/projects/recentIDs.json", name="api_recent_program_ids",
   *    defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'recent', false);
  }


  /**
   * @Route("/api/projects/mostDownloaded.json", name="api_most_downloaded_programs",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listMostDownloadedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'downloads');
  }


  /**
   * @Route("/api/projects/mostDownloadedIDs.json", name="api_most_downloaded_program_ids",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listMostDownloadedProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'downloads', false);
  }


  /**
   * @Route("/api/projects/mostViewed.json", name="api_most_viewed_programs",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listMostViewedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'views');
  }


  /**
   * @Route("/api/projects/mostViewedIDs.json", name="api_most_viewed_programids",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listMostViewedProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'views', false);
  }


  /**
   * @Route("/api/projects/randomPrograms.json", name="api_random_programs",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listRandomProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'random');
  }


  /**
   * @Route("/api/projects/randomProgramIDs.json", name="api_random_programids",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listRandomProgramIdsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'random', false);
  }


  /**
   * @Route("/api/projects/userPrograms.json", name="api_user_programs",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function listUserProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'user');
  }


  /**
   * @param Request $request
   * @param         $sortBy
   * @param bool    $details
   * @param string  $flavor
   *
   * @return ProgramListResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  private function listSortedPrograms(Request $request, $sortBy, $details = true, $flavor = 'pocketcode')
  {
    /**
     * @var ProgramManager $program_manager
     */

    $program_manager = $this->get('programmanager');
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $user_id = intval($request->query->get('user_id', 0));
    $max_version = $request->query->get('max_version', 0);

    // setting flavor to null to get all results
    if ($flavor == 'pocketcode')
    {
      $flavor = null;
    }

    if ($sortBy == 'downloads')
    {
      $programs = $program_manager->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor, $max_version);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getMostDownloadedPrograms('pocketcode', $limit - $count, $new_offset, $max_version));
      }
    }
    elseif ($sortBy == 'views')
    {
      $programs = $program_manager->getMostViewedPrograms($flavor, $limit, $offset, $max_version);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor, $max_version);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getMostViewedPrograms('pocketcode', $limit - $count, $new_offset, $max_version));
      }
    }
    elseif ($sortBy == 'user')
    {
      $programs = $program_manager->getUserPrograms($user_id, $max_version);
    }
    elseif ($sortBy == 'random')
    {
      $programs = $program_manager->getRandomPrograms($flavor, $limit, $offset, $max_version);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor, $max_version);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getRandomPrograms('pocketcode', $limit - $count, $new_offset, $max_version));
      }
    }
    else
    {
      $programs = $program_manager->getRecentPrograms($flavor, $limit, $offset, $max_version);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor, $max_version);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getRecentPrograms('pocketcode', $limit - $count, $new_offset, $max_version));
      }
    }

    if ($sortBy == 'user')
    {
      $numbOfTotalProjects = count($programs);
    }
    else
    {
      $numbOfTotalProjects = $program_manager->getTotalPrograms($flavor, $max_version);

      if ($flavor)
      {
        $numbOfTotalProjects += $program_manager->getTotalPrograms('pocketcode', $max_version);
      }
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects, $details);
  }
}
