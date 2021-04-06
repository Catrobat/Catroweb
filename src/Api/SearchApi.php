<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Search\SearchApiFacade;
use OpenAPI\Server\Api\SearchApiInterface;
use Symfony\Component\HttpFoundation\Response;

final class SearchApi extends AbstractApiController implements SearchApiInterface
{
  private SearchApiFacade $facade;

  public function __construct(SearchApiFacade $facade)
  {
    $this->facade = $facade;
  }

  /**
   * {@inheritDoc}
   */
  public function searchGet(string $query, ?string $type = 'all', ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null)
  {
    $type = $type ?? 'all';
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);

    // TODO: Implement notificationIdReadPut() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }
}
