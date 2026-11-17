<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Translation;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\Translation\TranslationResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMachineTranslation>
 */
class ProjectMachineTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectMachineTranslation::class);
  }

  /**
   * A translation of the project's current texts. Without a source language only auto-detected ones count.
   *
   * @return array<TranslationResult|null>|null
   */
  public function getCachedTranslation(Project $project, ?string $source_language, string $target_language): ?array
  {
    $criteria = new Criteria(firstResult: 0, accessRawFieldValues: true)
      ->where(Criteria::expr()->eq('project', $project))
      ->andWhere(Criteria::expr()->eq('target_language', $target_language))
      ->andWhere(Criteria::expr()->eq('source_hash', ProjectMachineTranslation::sourceHash($project)))
      ->andWhere(Criteria::expr()->neq('cached_name', null))
      // Several detected sources can be current (a picked source that matched one): always answer with the same.
      ->orderBy(['id' => Order::Ascending])
    ;
    if (null === $source_language) {
      $criteria->andWhere(Criteria::expr()->eq('auto_detected', true));
    } else {
      $criteria->andWhere(Criteria::expr()->eq('source_language', $source_language));
    }

    $entries = $this->matching($criteria);

    if ($entries->isEmpty()) {
      return null;
    }

    /** @var ProjectMachineTranslation $entry */
    $entry = $entries[0];

    $result = [];
    foreach ([$entry->getCachedName(), $entry->getCachedDescription(), $entry->getCachedCredits()] as $translation) {
      if (null === $translation) {
        $result[] = null;
        continue;
      }

      $translation_result = new TranslationResult();
      $translation_result->translation = $translation;
      $translation_result->cache = 'db';
      if (null === $source_language) {
        $translation_result->detected_source_language = $entry->getSourceLanguage();
      }

      $result[] = $translation_result;
    }

    return $result;
  }

  public function cacheTranslation(Project $project, string $source_language, string $target_language, bool $auto_detected, string $name, ?string $description = null, ?string $credits = null): void
  {
    $entry = $this->findOneBy(['project' => $project, 'source_language' => $source_language, 'target_language' => $target_language])
      ?? new ProjectMachineTranslation($project, $source_language, $target_language);
    // Same source and target give the same text: a picked source that matches a detected one stays usable as detected.
    $auto_detected = $auto_detected || $entry->isCurrentAutoDetected();
    // A translation can be longer than the original name; cached_name is VARCHAR(300) like the name itself.
    $entry->setCachedTranslation(mb_substr($name, 0, 300), $description, $credits, ProjectMachineTranslation::sourceHash($project), $auto_detected);
    $this->getEntityManager()->persist($entry);
    try {
      $this->getEntityManager()->flush();
    } catch (UniqueConstraintViolationException) {
      // A concurrent first translation of the same project and languages cached it already.
    }
  }

  public function invalidateCachedTranslation(Project $project): void
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
