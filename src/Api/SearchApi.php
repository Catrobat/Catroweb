<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Search\SearchApiFacade;
use OpenAPI\Server\Api\SearchApiInterface;
use OpenAPI\Server\Model\SearchResponse;
use Symfony\Component\HttpFoundation\Response;

class SearchApi extends AbstractApiController implements SearchApiInterface
{
  public function __construct(private readonly SearchApiFacade $facade)
  {
  }

  /**
   * @throws \JsonException
   */
  public function searchGet(string $query, string $type, int $limit, int $offset, int &$responseCode, array &$responseHeaders): array|SearchResponse
  {
    if ('' === $query || ctype_space($query)) {
      return [];
    }

    switch ($type) {
      case 'projects':
        $projects = $this->facade->getProjectManager()->search($query, $limit, $offset);
        $projects_total = $this->facade->getProjectManager()->searchCount($query);

        $result = $this->facade->getResponseManager()->getProjectsSearchResponse($projects, $projects_total);
        break;
      case 'users':
        $users = $this->facade->getUserManager()->search($query, $limit, $offset);
        $users_total = $this->facade->getUserManager()->searchCount($query);
        $result = $this->facade->getResponseManager()->getUsersSearchResponse($users, $users_total);
        break;
      case 'all':
      default:
        $projects = $this->facade->getProjectManager()->search($query, $limit, $offset);
        $projects_total = $this->facade->getProjectManager()->searchCount($query);
        $projects_response = $this->facade->getResponseManager()->getProjectsSearchResponse($projects, $projects_total);

        $users = $this->facade->getUserManager()->search($query, $limit, $offset);
        $users_total = $this->facade->getUserManager()->searchCount($query);
        $users_response = $this->facade->getResponseManager()->getUsersSearchResponse($users, $users_total);

        $result = $this->facade->getResponseManager()->getSearchResponse($projects_response, $users_response);
        break;
    }

    $responseHeaders['X-Response-Hash'] = md5(json_encode($result, JSON_THROW_ON_ERROR));

    $responseCode = Response::HTTP_OK;

    return $result;
  }
}
