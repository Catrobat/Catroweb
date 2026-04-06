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
    if (null === $user) {
      return;
    }

    $download_repo = $this->entity_manager->getRepository(ProgramDownloads::class);
    $download = $download_repo->findOneBy(['program' => $project, 'user' => $user, 'type' => ProgramDownloads::TYPE_PROJECT]);
    if (null !== $download) {
      return;
    }

    $this->entity_manager
      ->createQuery('UPDATE App\DB\Entity\Project\Program p SET p.downloads = p.downloads + 1 WHERE p.id = :pid')
      ->setParameter('pid', $project->getId())
      ->execute()
    ;

    $this->addDownloadEntry($project, $user, ProgramDownloads::TYPE_PROJECT);
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
