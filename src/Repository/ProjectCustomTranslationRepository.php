<?php

namespace App\Repository;

use App\Entity\Program;
use App\Entity\Translation\ProjectCustomTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class ProjectCustomTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProjectCustomTranslation::class);
  }

  public function addNameTranslation(Program $project, string $language, string $name_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 != $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setName($name_translation);
      try {
        $this->getEntityManager()->persist($translation);
        $this->getEntityManager()->flush();
      } catch (ORMException $e) {
        return false;
      }
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

  public function addDescriptionTranslation(Program $project, string $language, string $description_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 != $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setDescription($description_translation);
      try {
        $this->getEntityManager()->persist($translation);
        $this->getEntityManager()->flush();
      } catch (ORMException $e) {
        return false;
      }
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

  public function addCreditTranslation(Program $project, string $language, string $credit_translation): bool
  {
    $entry_count = $this->count($this->getCriteria($project, $language));

    if (1 != $entry_count) {
      $translation = new ProjectCustomTranslation($project, $language);
      $translation->setCredits($credit_translation);
      try {
        $this->getEntityManager()->persist($translation);
        $this->getEntityManager()->flush();
      } catch (ORMException $e) {
        return false;
      }
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

  public function getNameTranslation(Program $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return null === $translation ? null : $translation->getName();
  }

  public function getDescriptionTranslation(Program $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return null === $translation ? null : $translation->getDescription();
  }

  public function getCreditTranslation(Program $project, string $language): ?string
  {
    $translation = $this->findTranslation($project, $language);

    return null === $translation ? null : $translation->getCredits();
  }

  private function findTranslation(Program $project, string $language): ?ProjectCustomTranslation
  {
    return $this->findOneBy($this->getCriteria($project, $language));
  }

  private function getCriteria(Program $project, string $language): array
  {
    return ['project' => $project, 'language' => $language];
  }
}
