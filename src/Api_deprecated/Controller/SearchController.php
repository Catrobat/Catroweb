<?php

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProgramListResponse;
use App\Catrobat\Requests\AppRequest;
use App\Manager\ProgramManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class SearchController extends AbstractController
{
  protected AppRequest $app_request;
  private int $DEFAULT_LIMIT = 20;

  private int $DEFAULT_OFFSET = 0;

  public function __construct(AppRequest $app_request)
  {
    $this->app_request = $app_request;
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"},
   * methods={"GET"})
   *
   * @throws Exception
   */
  public function searchProgramsAction(Request $request, ProgramManager $program_manager, LoggerInterface $searchLogger): ProgramListResponse
  {
    $query = $request->query->get('q');

    $username = $this->getUser() ? $this->getUser()->getUsername() : '-';
    $searchLogger->debug("User: {$username}, Query: {$query}");

    $query = str_replace('yahoo', '', $query);
    $query = str_replace('gmail', '', $query);
    $query = str_replace('gmx', '', $query);
    $query = trim($query);

    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $max_version = $request->query->get('max_version', '');

    if ('' === $query || ctype_space($query)) {
      return new ProgramListResponse([], 0);
    }

    // we can't count the results since we apply limit and offset.
    // so we indeed have to use a separate query that ignores
    // limit and offset to get the number of results.

    try {
      $programs = $program_manager->search(
        $query, $limit, $offset, $max_version, null, $this->app_request->isDebugBuildRequest()
      );
      $numbOfTotalProjects = $program_manager->searchCount(
        $query, $max_version, null, $this->app_request->isDebugBuildRequest()
      );
    } catch (Exception $e) {
      $programs = [];
      $numbOfTotalProjects = 0;
    }

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
    $tag_name = (string) $request->query->get('q', 0);
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $programs = $program_manager->getProgramsByTagInternalTitle($tag_name, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchTagCount($tag_name);

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
    $programs = $program_manager->getProjectsByExtensionInternalTitle($query, $limit, $offset);

    $numbOfTotalProjects = $program_manager->searchExtensionCount($query);

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }
}
