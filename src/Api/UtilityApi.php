<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;

class UtilityApi extends AbstractApiController implements UtilityApiInterface
{
  public function __construct(private readonly UtilityApiFacade $facade)
  {
  }

  public function healthGet(int &$responseCode, array &$responseHeaders): void
  {
    $responseCode = Response::HTTP_NO_CONTENT;
  }

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
