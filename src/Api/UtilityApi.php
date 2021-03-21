<?php

namespace App\Api;

use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use Symfony\Component\HttpFoundation\Response;

class UtilityApi implements UtilityApiInterface
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }

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
    $survey = $this->getActiveSurvey($lang_code);

    if (null === $survey) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $response = new SurveyResponse([
      'url' => $survey->getUrl(),
    ]);

    $responseCode = Response::HTTP_OK;
    $responseHeaders['X-Response-Hash'] = md5(json_encode($response));

    return $response;
  }

  protected function getActiveSurvey(string $lang_code): ?Survey
  {
    $survey_repo = $this->entity_manager->getRepository(Survey::class);

    return $survey_repo->findOneBy(['language_code' => $lang_code, 'active' => true]);
  }
}
