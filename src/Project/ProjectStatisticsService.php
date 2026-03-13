<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramDownloads;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class ProjectStatisticsService
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function increaseViews(Program $project): void
  {
    $this->entity_manager
      ->createQuery('UPDATE App\DB\Entity\Project\Program p SET p.views = p.views + 1 WHERE p.id = :pid')
      ->setParameter('pid', $project->getId())
      ->execute()
    ;
  }

  public function increaseDownloads(Program $project, ?User $user): void
  {
    $this->increaseNumberOfDownloads($project, $user, ProgramDownloads::TYPE_PROJECT);
  }

  public function increaseApkDownloads(Program $project, ?User $user): void
  {
    $this->increaseNumberOfDownloads($project, $user, ProgramDownloads::TYPE_APK);
  }

  private function increaseNumberOfDownloads(Program $project, ?User $user, string $download_type): void
  {
    if (null === $user) {
      return;
    }

    $download_repo = $this->entity_manager->getRepository(ProgramDownloads::class);
    // No matter which type it should only count once!
    $download = $download_repo->findOneBy(['program' => $project, 'user' => $user, 'type' => $download_type]);
    if (null !== $download) {
      return;
    }

    // the simplified DQL is the only solution that guarantees proper count: https://stackoverflow.com/questions/24681613/doctrine-entity-increase-value-download-counter
    $column = match ($download_type) {
      ProgramDownloads::TYPE_PROJECT => 'p.downloads',
      ProgramDownloads::TYPE_APK => 'p.apk_downloads',
      default => null,
    };

    if (null !== $column) {
      $this->entity_manager
        ->createQuery("UPDATE App\\DB\\Entity\\Project\\Program p SET {$column} = {$column} + 1 WHERE p.id = :pid")
        ->setParameter('pid', $project->getId())
        ->execute()
      ;
    }

    $this->addDownloadEntry($project, $user, $download_type);
  }

  private function addDownloadEntry(Program $project, ?User $user, string $download_type): void
  {
    $download = new ProgramDownloads();
    $download->setUser($user);
    $download->setProgram($project);
    $download->setType($download_type);
    $download->setDownloadedAt(new \DateTime('now'));

    $this->entity_manager->persist($download);
    $this->entity_manager->flush();
  }
}
