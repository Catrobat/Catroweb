<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class SearchController
 * @package App\Catrobat\Controller\Api
 */
class SearchController extends Controller
{

  /**
   * @var int
   */
  private $DEFAULT_LIMIT = 20;

  /**
   * @var int
   */
  private $DEFAULT_OFFSET = 0;


  /**
   * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"},
   *    methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function searchProgramsAction(Request $request)
  {
    /** @var ProgramManager $program_manager */
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');

    $query = str_replace("yahoo", "", $query);
    $query = str_replace("gmail", "", $query);
    $query = str_replace("gmx", "", $query);
    $query = trim($query);

    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));

    $programs = $program_manager->search($query, $limit, $offset);
    // we can't count the results since we apply limit and offset.
    // so we indeed have to use a separate query that ignores
    // limit and offset to get the number of results.
    $numbOfTotalProjects = $program_manager->searchCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }


  /**
   * @Route("/api/projects/search/tagPrograms.json", name="api_search_tag",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function tagSearchProgramsAction(Request $request)
  {
    /** @var ProgramManager $program_manager */
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');
    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));
    $programs = $program_manager->getProgramsByTagId($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchTagCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }


  /**
   * @Route("/api/projects/search/extensionPrograms.json", name="api_search_extension",
   *                                                       defaults={"_format": "json"},
   *                                                       methods={"GET"})
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function extensionSearchProgramsAction(Request $request)
  {
    /** @var ProgramManager $program_manager */
    $program_manager = $this->get('programmanager');
    $query = $request->query->get('q');
    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));
    $programs = $program_manager->getProgramsByExtensionName($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchExtensionCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

}
