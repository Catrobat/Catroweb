<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use App\Api\Traits\CursorPaginationTrait;
use App\Api\Traits\KeysetCursorTrait;
use Doctrine\DBAL\Connection;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\FeaturedBannersListResponse;
use OpenAPI\Server\Model\HealthResponse;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Locales;

class UtilityApi extends AbstractApiController implements UtilityApiInterface
{
  use CursorPaginationTrait;
  use KeysetCursorTrait;

  public function __construct(
    private readonly UtilityApiFacade $facade,
    private readonly Connection $connection,
  ) {
  }

  #[\Override]
  public function featuredBannersGet(int $limit, ?string $cursor, ?string $flavor, int &$responseCode, array &$responseHeaders): FeaturedBannersListResponse
  {
    $limit = min(max($limit, 1), 50);

    $cursor_data = $this->decodeIntKeysetCursor($cursor);
    if (null === $cursor_data && null !== $cursor && '' !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      $response = new FeaturedBannersListResponse();
      $response->setData([]);
      $response->setNextCursor(null);
      $response->setHasMore(false);

      return $response;
    }

    $banners = $this->facade->getLoader()->getActiveBannersKeyset(
      $limit + 1, $cursor_data['value'] ?? null, $cursor_data['id'] ?? null, $flavor
    );

    $has_more = count($banners) > $limit;
    if ($has_more) {
      array_pop($banners);
    }

    $banner_responses = array_map(
      fn ($banner) => $this->facade->getResponseManager()->createFeaturedBannerResponse($banner),
      $banners,
    );

    $next_cursor = null;
    if ($has_more && [] !== $banners) {
      $last = end($banners);
      $last_id = $last->getId();
      if (null !== $last_id) {
        $next_cursor = $this->encodeIntKeysetCursor($last->getPriority(), $last_id);
      }
    }

    $responseCode = Response::HTTP_OK;

    $response = new FeaturedBannersListResponse();
    $response->setData($banner_responses);
    $response->setNextCursor($next_cursor);
    $response->setHasMore($has_more);

    return $response;
  }

  #[\Override]
  public function healthGet(int &$responseCode, array &$responseHeaders): HealthResponse
  {
    $dbStatus = 'ok';

    try {
      $this->connection->executeQuery('SELECT 1');
    } catch (\Throwable) {
      $dbStatus = 'error';
    }

    $overallStatus = 'error' === $dbStatus ? 'degraded' : 'ok';
    $responseCode = 'ok' === $overallStatus ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

    $response = new HealthResponse();
    $response->setStatus($overallStatus);
    $response->setTimestamp(new \DateTime('now', new \DateTimeZone('UTC')));
    $response->setDatabase($dbStatus);

    return $response;
  }

  #[\Override]
  public function languagesGet(string $accept_language, int &$responseCode, array &$responseHeaders): array
  {
    $locale = \Locale::lookup(
      array_keys(Locales::getNames()),
      str_replace('-', '_', $accept_language),
      true,
      'en',
    );

    $locales = [];
    foreach (Locales::getNames($locale) as $key => $value) {
      $len = strlen((string) $key);
      if (2 === $len || 5 === $len) {
        $locales[str_replace('_', '-', (string) $key)] = $value;
      }
    }

    $etag = '"'.$locale.'"';
    $responseHeaders['ETag'] = $etag;
    $responseCode = Response::HTTP_OK;

    return $locales;
  }

  #[\Override]
  public function surveyLangCodeGet(string $lang_code, string $flavor, string $platform, int &$responseCode, array &$responseHeaders): ?SurveyResponse
  {
    $criteria = [];
    $criteria['language_code'] = $lang_code;
    $criteria['active'] = true;

    if ('' !== trim($flavor)) {
      $flavor_obj = $this->facade->getLoader()->getSurveyFlavor($flavor);
      if (is_null($flavor_obj)) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return null;
      }

      $criteria['flavor'] = $flavor_obj;
    }

    if ('' !== trim($platform)) {
      $available_platforms = ['ios', 'android'];
      $platform = strtolower($platform);
      if ('' !== trim($platform) && !in_array($platform, $available_platforms, true)) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return null;
      }

      $criteria['platform'] = $platform;
    }

    $survey = $this->facade->getLoader()->getSurvey($criteria);

    if (is_null($survey)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createSurveyResponse($survey);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }
}
