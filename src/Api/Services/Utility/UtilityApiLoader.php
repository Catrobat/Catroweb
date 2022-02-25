<?php

namespace App\Api\Services\Utility;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;

final class UtilityApiLoader extends AbstractApiLoader
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }

  public function getActiveSurvey(string $lang_code): ?Survey
  {
    $survey_repo = $this->entity_manager->getRepository(Survey::class);

    return $survey_repo->findOneBy(['language_code' => $lang_code, 'active' => true]);
  }
}
