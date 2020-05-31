<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\ProgramManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
  private int $DEFAULT_LIMIT = 20;

  private int $DEFAULT_OFFSET = 0;

  /**
   * @deprecated
   *
   * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"},
   * methods={"GET"})
   *
   * @throws Exception
   */
  public function searchProgramsAction(Request $request, ProgramManager $program_manager): ProgramListResponse
  {
    $query = $request->query->get('q');

    $query = str_replace('yahoo', '', $query);
    $query = str_replace('gmail', '', $query);
    $query = str_replace('gmx', '', $query);
    $query = trim($query);

    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $max_version = $request->query->get('max_version', '0');

    if ('' === $query || ctype_space($query))
    {
      return new ProgramListResponse([], 0);
    }

    $programs = $program_manager->search($query, $limit, $offset, $max_version);
    // we can't count the results since we apply limit and offset.
    // so we indeed have to use a separate query that ignores
    // limit and offset to get the number of results.

    $numbOfTotalProjects = $program_manager->searchCount($query, $max_version);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/search/tagProjects.json", name="api_search_tag",
   * defaults={"_format": "json"}, methods={"GET"})
   */
  public function tagSearchProgramsAction(Request $request, ProgramManager $program_manager): ProgramListResponse
  {
    $query = $request->query->get('q');
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $programs = $program_manager->getProgramsByTagId($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchTagCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/search/extensionProjects.json", name="api_search_extension",
   * defaults={"_format": "json"}, methods={"GET"})
   */
  public function extensionSearchProgramsAction(Request $request, ProgramManager $program_manager): ProgramListResponse
  {
    $query = $request->query->get('q');
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $programs = $program_manager->getProgramsByExtensionName($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchExtensionCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }
}
