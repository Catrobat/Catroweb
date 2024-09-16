<?php

declare(strict_types=1);

namespace App\Project\EntityListener;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\TagRepository;
use App\DB\EntityRepository\Translation\ProjectMachineTranslationRepository;
use App\User\Achievements\AchievementManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Program::class)]
class ProjectPostUpdateEntityListener
{
  public function __construct(
    protected AchievementManager $achievement_manager,
    protected TagRepository $tag_repository,
    private readonly ProjectMachineTranslationRepository $machine_translation_repository,
  ) {
  }

  /**
   * @throws \Exception
   */
  public function postUpdate(Program $project, PostUpdateEventArgs $args): void
  {
    $user = $project->getUser();
    $this->addCodingJam092021Achievement($project, $user);
    $this->invalidateTranslationCacheIfNecessary($project);
  }

  /**
   * @throws \Exception
   */
  protected function addCodingJam092021Achievement(Program $project, User $user): void
  {
    $tags = $project->getTags();
    /** @var Tag $tag */
    foreach ($tags as $tag) {
      if (Tag::CODING_JAM_09_2021 === $tag->getInternalTitle()) {
        $this->achievement_manager->unlockAchievementCodingJam092021($user);
        break;
      }
    }

    if (str_contains($project->getDescription() ?? '', '#catrobatfestival2021')) {
      $coding_jam_09_2021_tag = $this->tag_repository->findOneBy(['internal_title' => Tag::CODING_JAM_09_2021]);
      if (!$project->getTags()->contains($coding_jam_09_2021_tag)) {
        $project->addTag($coding_jam_09_2021_tag);
      }

      $this->achievement_manager->unlockAchievementCodingJam092021($user);
    }
  }

  private function invalidateTranslationCacheIfNecessary(Program $project): void
  {
    if ($project->shouldInvalidateTranslationCache()) {
      $this->machine_translation_repository->invalidateCachedTranslation($project);
    }
  }
}
