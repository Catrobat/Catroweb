<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectDownloads;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class ProjectStatisticsService
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  public function increaseViews(Project $project): void
  {
    $this->entity_manager
      ->createQuery('UPDATE App\DB\Entity\Project\Project p SET p.views = p.views + 1 WHERE p.id = :pid')
      ->setParameter('pid', $project->getId())
      ->execute()
    ;
  }

  public function increaseDownloads(Project $project, ?User $user): void
  {
    if (null === $user) {
      return;
    }

    $download_repo = $this->entity_manager->getRepository(ProjectDownloads::class);
    $download = $download_repo->findOneBy(['project' => $project, 'user' => $user, 'type' => ProjectDownloads::TYPE_PROJECT]);
    if (null !== $download) {
      return;
    }

    $this->entity_manager
      ->createQuery('UPDATE App\DB\Entity\Project\Project p SET p.downloads = p.downloads + 1 WHERE p.id = :pid')
      ->setParameter('pid', $project->getId())
      ->execute()
    ;

    $this->addDownloadEntry($project, $user, ProjectDownloads::TYPE_PROJECT);
  }

  private function addDownloadEntry(Project $project, ?User $user, string $download_type): void
  {
    $download = new ProjectDownloads();
    $download->setUser($user);
    $download->setProject($project);
    $download->setType($download_type);
    $download->setDownloadedAt(new \DateTime('now'));

    $this->entity_manager->persist($download);
    $this->entity_manager->flush();
  }
}
