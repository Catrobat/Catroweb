<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Translation;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\Translation\TranslationResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class ProjectMachineTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectMachineTranslation::class);
  }

  public function getCachedTranslation(Program $project, ?string $source_language, string $target_language): ?array
  {
    $criteria = Criteria::create()
      ->where(Criteria::expr()->eq('project', $project))
      ->andWhere(Criteria::expr()->eq('target_language', $target_language))
      ->andWhere(Criteria::expr()->neq('cached_name', null))
    ;
    if (null !== $source_language) {
      $criteria->andWhere(Criteria::expr()->eq('source_language', $source_language));
    }
    $entries = $this->matching($criteria);

    if ($entries->isEmpty()) {
      return null;
    }

    /** @var ProjectMachineTranslation $entry */
    $entry = $entries[0];

    $translations = [
      $entry->getCachedName(),
      $entry->getCachedDescription(),
      $entry->getCachedCredits(),
    ];

    $result = [];

    foreach ($translations as $translation) {
      if (null === $translation) {
        $result[] = null;
        continue;
      }

      $translation_result = new TranslationResult();
      $translation_result->translation = $translation;
      $translation_result->provider = $entry->getProvider();
      $translation_result->cache = 'db';
      if (null === $source_language) {
        $translation_result->detected_source_language = $entry->getSourceLanguage();
      }

      $result[] = $translation_result;
    }

    return $result;
  }

  public function invalidateCachedTranslation(Program $project): void
  {
    /** @var ProjectMachineTranslation[] $entries */
    $entries = $this->findBy(['project' => $project]);

    foreach ($entries as $entry) {
      $entry->invalidateCachedTranslation();
      $this->getEntityManager()->persist($entry);
    }
    $this->getEntityManager()->flush();
  }
}
