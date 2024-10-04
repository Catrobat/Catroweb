<?php

declare(strict_types=1);

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\System\Survey;
use OpenAPI\Server\Model\SurveyResponse;

class UtilityResponseManager extends AbstractResponseManager
{
  public function createSurveyResponse(Survey $survey): SurveyResponse
  {
    return new SurveyResponse([
      'url' => $survey->getUrl(),
    ]);
  }
}
