<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use OpenAPI\Server\Api\UtilityApiInterface;
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
  public function surveyLangCodeGet(string $lang_code, string $flavor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $survey = $this->facade->getLoader()->getActiveSurvey($lang_code);

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
