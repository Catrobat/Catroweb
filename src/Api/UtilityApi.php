<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use Doctrine\DBAL\Connection;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\FeaturedBannerResponse;
use OpenAPI\Server\Model\HealthResponse;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;

class UtilityApi extends AbstractApiController implements UtilityApiInterface
{
  public function __construct(
    private readonly UtilityApiFacade $facade,
    private readonly Connection $connection,
  ) {
  }

  /**
   * @return FeaturedBannerResponse[]
   */
  #[\Override]
  public function featuredBannersGet(int $limit, int $offset, int &$responseCode, array &$responseHeaders): array
  {
    $limit = min(max($limit, 1), 50);
    $offset = max($offset, 0);

    $banners = $this->facade->getLoader()->getActiveBanners($limit, $offset);

    $responseCode = Response::HTTP_OK;

    return array_map(
      fn ($banner): FeaturedBannerResponse => $this->facade->getResponseManager()->createFeaturedBannerResponse($banner),
      $banners,
    );
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
