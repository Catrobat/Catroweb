<?php

namespace App\Api;

use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;

class UtilityApi implements UtilityApiInterface
{
  /**
   * {@inheritdoc}
   */
  public function healthGet(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function surveyLangCodeGet(string $lang_code, &$responseCode, array &$responseHeaders)
  {
    return new SurveyResponse(['url' => 'https://www.surveylegend.com/s/2yaq']);
  }
}
