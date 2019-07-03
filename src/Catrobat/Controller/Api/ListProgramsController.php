<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\ProgramManager;
use Doctrine\ORM\NonUniqueResultException;
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
   * @throws NonUniqueResultException
   */
  public function listProgramsAction(Request $request, $flavor)
  {
    if ($flavor == 'pocketcode')
    {
      $flavor = null;
    }

    return $this->listSortedPrograms($request, 'recent', true, false, $flavor);
  }


  /**
   * @Route("/api/projects/recentIDs.json", name="api_recent_program_ids",
   *    defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
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
   * @throws NonUniqueResultException
   */
  public function listUserProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, 'user');
  }


  /**
   * @param Request $request
   * @param         $sortBy
   * @param bool    $details
   * @param bool    $useRequestFlavor
   * @param string  $flavor
   *
   * @return ProgramListResponse
   * @throws NonUniqueResultException
   */
  private function listSortedPrograms(Request $request, $sortBy, $details = true, $useRequestFlavor = true, $flavor = null)
  {
    if ($useRequestFlavor === true)
    {
      $flavor = $request->attributes->get('flavor');
    }

    /**
     * @var ProgramManager $program_manager
     */
    $program_manager = $this->get('programmanager');

    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $user_id = intval($request->query->get('user_id', 0));

    if ($sortBy == 'downloads')
    {
      $programs = $program_manager->getMostDownloadedPrograms($flavor, $limit, $offset);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getMostDownloadedPrograms(
          '!' . $flavor, $limit - $count, $new_offset
        ));
      }
    }
    elseif ($sortBy == 'views')
    {
      $programs = $program_manager->getMostViewedPrograms($flavor, $limit, $offset);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getMostViewedPrograms(
          '!' . $flavor, $limit - $count, $new_offset
        ));
      }
    }
    elseif ($sortBy == 'user')
    {
      $programs = $program_manager->getUserPrograms($user_id);
    }
    elseif ($sortBy == 'random')
    {
      $programs = $program_manager->getRandomPrograms($flavor, $limit, $offset);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getRandomPrograms(
          '!' . $flavor, $limit - $count, $new_offset
        ));
      }
    }
    else
    {
      $programs = $program_manager->getRecentPrograms($flavor, $limit, $offset);

      $count = count($programs);
      if ($count != $limit && $flavor)
      {
        $flavor_count = $program_manager->getTotalPrograms($flavor);
        $new_offset = max($offset - $flavor_count + $count, 0);
        $programs = array_merge($programs, $program_manager->getRecentPrograms(
          '!' . $flavor, $limit - $count, $new_offset
        ));
      }
    }

    if ($sortBy == 'user')
    {
      $numbOfTotalProjects = count($programs);
    }
    else
    {
      $numbOfTotalProjects = $program_manager->getTotalPrograms($flavor);

      if ($flavor)
      {
        $numbOfTotalProjects += $program_manager->getTotalPrograms('!' . $flavor);
      }
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects, $details);
  }
}
