<?php

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\Entity\Survey;
use OpenAPI\Server\Model\SurveyResponse;

final class UtilityResponseManager extends AbstractResponseManager
{
  public function createSurveyResponse(Survey $survey): SurveyResponse
  {
    return new SurveyResponse([
      'url' => $survey->getUrl(),
    ]);
  }
}
