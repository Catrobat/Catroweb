<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;

final class UtilityApi extends AbstractApiController implements UtilityApiInterface
{
  public function __construct(private readonly UtilityApiFacade $facade)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function healthGet(int &$responseCode, array &$responseHeaders): void
  {
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  /**
   * {@inheritdoc}
   */
  public function surveyLangCodeGet(string $lang_code, string $flavor, string $platform, int &$responseCode, array &$responseHeaders): ?SurveyResponse
  {
    $flavor_obj = null;

    $criteria = [];
    $criteria['language_code'] = $lang_code;
    $criteria['active'] = true;

    if (trim($flavor) !== '') {
      $flavor_obj = $this->facade->getLoader()->getSurveyFlavor($flavor);
      if (is_null($flavor_obj)) {
        $responseCode = Response::HTTP_NOT_FOUND;
        return null;
      }
      $criteria['flavor'] = $flavor_obj;
    }

    if (trim($platform) !== '') {
      $available_platforms = array('ios', 'android');
      $platform = strtolower($platform);
      if (trim($platform) !== '' && !in_array($platform, $available_platforms)) {
        $responseCode = Response::HTTP_NOT_FOUND;
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
