<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Translation;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectCustomTranslation>
 */
class ProjectCustomTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $manager_registry)
  {
    parent::__construct($manager_registry, ProjectCustomTranslation::class);
  }

  public function addNameTranslation(Project $project, string $language, string $name_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 !== $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setName($name_translation);
      $this->getEntityManager()->persist($translation);
      $this->getEntityManager()->flush();
    } else {
      $qb = $this->createQueryBuilder('t');

      $qb->update()
        ->set('t.name', ':name')
        ->where($qb->expr()->eq('t.project', ':project'))
        ->andWhere($qb->expr()->eq('t.language', ':language'))
        ->setParameter(':project', $project)
        ->setParameter(':language', $language)
        ->setParameter(':name', $name_translation)
        ->getQuery()
        ->execute()
      ;
    }

    return true;
  }

  public function addDescriptionTranslation(Project $project, string $language, string $description_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 !== $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setDescription($description_translation);
      $this->getEntityManager()->persist($translation);
      $this->getEntityManager()->flush();
    } else {
      $qb = $this->createQueryBuilder('t');

      $qb->update()
        ->set('t.description', ':description')
        ->where($qb->expr()->eq('t.project', ':project'))
        ->andWhere($qb->expr()->eq('t.language', ':language'))
        ->setParameter(':project', $project)
        ->setParameter(':language', $language)
        ->setParameter(':description', $description_translation)
        ->getQuery()
        ->execute()
      ;
    }

    return true;
  }

  public function addCreditTranslation(Project $project, string $language, string $credit_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 !== $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setCredits($credit_translation);
      $this->getEntityManager()->persist($translation);
      $this->getEntityManager()->flush();
    } else {
      $qb = $this->createQueryBuilder('t');

      $qb->update()
        ->set('t.credits', ':credit')
        ->where($qb->expr()->eq('t.project', ':project'))
        ->andWhere($qb->expr()->eq('t.language', ':language'))
        ->setParameter(':project', $project)
        ->setParameter(':language', $language)
        ->setParameter(':credit', $credit_translation)
        ->getQuery()
        ->execute()
      ;
    }

    return true;
  }

  public function getNameTranslation(Project $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return $translation?->getName();
  }

  public function getDescriptionTranslation(Project $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return $translation?->getDescription();
  }

  public function getCreditTranslation(Project $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return $translation?->getCredits();
  }

  public function deleteNameTranslation(Project $project, string $language): bool
  {
    return $this->clearField($project, $language, 'name');
  }

  public function deleteDescriptionTranslation(Project $project, string $language): bool
  {
    return $this->clearField($project, $language, 'description');
  }

  public function deleteCreditTranslation(Project $project, string $language): bool
  {
    return $this->clearField($project, $language, 'credits');
  }

  public function listDefinedLanguages(Project $project): array
  {
    $qb = $this->createQueryBuilder('t');

    $result = $qb->select('t.language')
      ->where($qb->expr()->eq('t.project', ':project'))
      ->setParameter(':project', $project)
      ->getQuery()
      ->execute()
    ;

    return array_map(
      static fn (array $e): mixed => $e['language'], $result
    );
  }

  /**
   * @psalm-param array<Project> $projects
   */
  public function countDefinedLanguages(array $projects): int
  {
    $languages = [];
    foreach ($projects as $project) {
      $languages = array_unique(array_merge($languages, $this->listDefinedLanguages($project)));
    }

    return count($languages);
  }

  /**
   * Clears one field and removes the row once all fields are empty, in the database itself:
   * the editor deletes name, description and credits with three parallel requests, and a
   * read-then-decide in PHP let every request see the other fields still set, leaving an
   * empty row behind.
   */
  private function clearField(Project $project, string $language, string $field): bool
  {
    $this->createQueryBuilder('t')
      ->update()
      ->set('t.'.$field, 'NULL')
      ->where('t.project = :project')
      ->andWhere('t.language = :language')
      ->setParameter('project', $project)
      ->setParameter('language', $language)
      ->getQuery()
      ->execute()
    ;

    $this->createQueryBuilder('t')
      ->delete()
      ->where('t.project = :project')
      ->andWhere('t.language = :language')
      ->andWhere('t.name IS NULL')
      ->andWhere('t.description IS NULL')
      ->andWhere('t.credits IS NULL')
      ->setParameter('project', $project)
      ->setParameter('language', $language)
      ->getQuery()
      ->execute()
    ;

    return true;
  }

  private function findTranslation(Project $project, string $language): ?ProjectCustomTranslation
  {
    return $this->findOneBy($this->getCriteria($project, $language));
  }

  private function getCriteria(Project $project, string $language): array
  {
    return ['project' => $project, 'language' => $language];
  }
}
