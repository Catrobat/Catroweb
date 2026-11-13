<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tells an owner that the files of some of their projects were lost and must be re-uploaded.
 * The affected projects (id => name at notice time) are kept as JSON in the inherited message column.
 */
#[ORM\Entity]
class ProjectFilesLostNotification extends CatroNotification
{
  /**
   * @param array<string, string> $projects project id => project name
   */
  public function __construct(User $user, array $projects = [])
  {
    parent::__construct($user, '', json_encode($projects, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'project_files_lost');
  }

  /**
   * @param iterable<ProjectFilesLostNotification> $notices
   *
   * @return array<string, \DateTimeImmutable> project id => creation time of the newest notice listing it
   *                                           (only notices created no later than $until count)
   */
  public static function latestNoticePerProject(iterable $notices, ?\DateTimeInterface $until = null): array
  {
    $latest = [];
    foreach ($notices as $notice) {
      $created_at = $notice->getCreatedAt();
      if (null !== $until && $created_at > $until) {
        continue;
      }
      foreach (array_keys($notice->getProjects()) as $id) {
        if (!isset($latest[$id]) || $latest[$id] < $created_at) {
          $latest[$id] = $created_at;
        }
      }
    }

    return $latest;
  }

  /**
   * @return array<string, string> project id => project name
   */
  public function getProjects(): array
  {
    $projects = json_decode($this->getMessage(), true);
    if (!is_array($projects)) {
      return [];
    }

    $result = [];
    foreach ($projects as $id => $name) {
      $result[(string) $id] = (string) $name;
    }

    return $result;
  }
}
