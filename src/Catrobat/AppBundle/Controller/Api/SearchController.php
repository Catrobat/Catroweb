<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Responses\ProgramListResponse;

class SearchController extends Controller
{

  /**
   * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"},
   *                                     methods={"GET"})
   */
  public function searchProgramsAction(Request $request)
  {
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');

    $query = str_replace("yahoo", "", $query);
    $query = str_replace("gmail", "", $query);
    $query = str_replace("gmx", "", $query);
    $query = trim($query);

    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $max_version = $request->query->get('max_version', 0);

    $programs = $program_manager->search($query, $limit, $offset, $max_version);
    // we can't count the results since we apply limit and offset.
    // so we indeed have to use a seperate query that ignores
    // limit and offset to get the number of results.
    $numbOfTotalProjects = $program_manager->searchCount($query, $max_version);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @Route("/api/projects/search/tagPrograms.json", name="api_search_tag", defaults={"_format":
   *                                                 "json"}, methods={"GET"})
   */
  public function tagSearchProgramsAction(Request $request)
  {
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $programs = $program_manager->getProgramsByTagId($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchTagCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @Route("/api/projects/search/extensionPrograms.json", name="api_search_extension",
   *                                                       defaults={"_format": "json"},
   *                                                       methods={"GET"})
   */
  public function extensionSearchProgramsAction(Request $request)
  {
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $programs = $program_manager->getProgramsByExtensionName($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchExtensionCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  private function checkProgramVersion($programs, $i, $max_version)
  {
    $program_version = $programs[$i]->getLanguageVersion();
    if (version_compare($program_version, $max_version) > 0)
    {
      unset($programs[$i]);
    }
  }
}
