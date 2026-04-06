<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Search\SearchApiFacade;
use OpenAPI\Server\Api\SearchApiInterface;
use OpenAPI\Server\Model\SearchResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class SearchApi extends AbstractApiController implements SearchApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly SearchApiFacade $facade,
    private readonly RateLimiterFactory $searchBurstLimiter,
    private readonly RequestStack $request_stack,
  ) {
  }

  /**
   * @throws \JsonException
   */
  #[\Override]
  public function searchGet(string $query, string $type, int $limit, int $offset, ?string $cursor, int &$responseCode, array &$responseHeaders): array|SearchResponse
  {
    $ip = $this->request_stack->getCurrentRequest()?->getClientIp() ?? 'unknown';
    if (null === $this->checkIpRateLimit($ip, $this->searchBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return [];
    }

    if ('' === $query || ctype_space($query)) {
      return [];
    }

    switch ($type) {
      case 'projects':
        $projects = $this->facade->getProjectSearchService()->search($query, $limit, $offset);
        $projects_total = $this->facade->getProjectSearchService()->searchCount($query);

        $result = $this->facade->getResponseManager()->getProjectsSearchResponse($projects, $projects_total);
        break;
      case 'users':
        $users = $this->facade->getUserManager()->search($query, $limit, $offset);
        $users_total = $this->facade->getUserManager()->searchCount($query);
        $result = $this->facade->getResponseManager()->getUsersSearchResponse($users, $users_total);
        break;
      case 'studios':
        $studios = $this->facade->getStudioSearchService()->search($query, $limit, $offset);
        $studios_total = $this->facade->getStudioSearchService()->searchCount($query);
        $result = $this->facade->getResponseManager()->getStudiosSearchResponse($studios, $studios_total);
        break;
      case 'all':
      default:
        $projects = $this->facade->getProjectSearchService()->search($query, $limit, $offset);
        $projects_total = $this->facade->getProjectSearchService()->searchCount($query);
        $projects_response = $this->facade->getResponseManager()->getProjectsSearchResponse($projects, $projects_total);

        $users = $this->facade->getUserManager()->search($query, $limit, $offset);
        $users_total = $this->facade->getUserManager()->searchCount($query);
        $users_response = $this->facade->getResponseManager()->getUsersSearchResponse($users, $users_total);

        $studios = $this->facade->getStudioSearchService()->search($query, $limit, $offset);
        $studios_total = $this->facade->getStudioSearchService()->searchCount($query);
        $studios_response = $this->facade->getResponseManager()->getStudiosSearchResponse($studios, $studios_total);

        $result = $this->facade->getResponseManager()->getSearchResponse($projects_response, $users_response, $studios_response);
        break;
    }

    $responseHeaders['X-Response-Hash'] = md5(json_encode($result, JSON_THROW_ON_ERROR));

    $responseCode = Response::HTTP_OK;

    return $result;
  }
}
