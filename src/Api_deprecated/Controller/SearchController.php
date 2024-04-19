<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProjectListResponse;
use App\Project\ProjectManager;
use App\Utils\RequestHelper;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @deprecated
 */
class SearchController extends AbstractController
{
  private int $DEFAULT_LIMIT = 20;

  private int $DEFAULT_OFFSET = 0;

  public function __construct(protected RequestHelper $app_request)
  {
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/projects/search.json', name: 'api_search_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function searchProjects(Request $request, ProjectManager $project_manager, LoggerInterface $searchLogger): ProjectListResponse
  {
    $query = (string) $request->query->get('q', '');
    $username = $this->getUser() ? $this->getUser()->getUserIdentifier() : '-';
    $searchLogger->debug("User: {$username}, Query: {$query}");
    $query = str_replace('yahoo', '', $query);
    $query = str_replace('gmail', '', $query);
    $query = str_replace('gmx', '', $query);
    $query = trim($query);
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $max_version = (string) $request->query->get('max_version', '');
    if ('' === $query || ctype_space($query)) {
      return new ProjectListResponse([], 0);
    }
    // we can't count the results since we apply limit and offset.
    // so we indeed have to use a separate query that ignores
    // limit and offset to get the number of results.
    try {
      $projects = $project_manager->search(
        $query, $limit, $offset, $max_version, null, $this->app_request->isDebugBuildRequest()
      );
      $numbOfTotalProjects = $project_manager->searchCount(
        $query, $max_version, null, $this->app_request->isDebugBuildRequest()
      );
    } catch (\Exception) {
      $projects = [];
      $numbOfTotalProjects = 0;
    }

    return new ProjectListResponse($projects, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/search/tagProjects.json', name: 'api_search_tag', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function tagSearchProjects(Request $request, ProjectManager $project_manager): ProjectListResponse
  {
    $tag_name = (string) $request->query->get('q', 0);
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $projects = $project_manager->getProjectsByTagInternalTitle($tag_name, $limit, $offset);
    $numbOfTotalProjects = $project_manager->searchTagCount($tag_name);

    return new ProjectListResponse($projects, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/search/extensionProjects.json', name: 'api_search_extension', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function extensionSearchProjects(Request $request, ProjectManager $project_manager): ProjectListResponse
  {
    $query = (string) $request->query->get('q');
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $projects = $project_manager->getProjectsByExtensionInternalTitle($query, $limit, $offset);
    $numbOfTotalProjects = $project_manager->searchExtensionCount($query);

    return new ProjectListResponse($projects, $numbOfTotalProjects);
  }
}
