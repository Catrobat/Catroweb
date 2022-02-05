<?php

namespace App\Project\EventListener;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\TagRepository;
use App\User\Achievements\AchievementManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class ProjectPostUpdateNotifier
{
  protected AchievementManager $achievement_manager;
  protected TagRepository $tag_repository;

  public function __construct(AchievementManager $achievement_manager, TagRepository $tag_repository)
  {
    $this->achievement_manager = $achievement_manager;
    $this->tag_repository = $tag_repository;
  }

  public function postUpdate(Program $project, LifecycleEventArgs $event): void
  {
    $user = $project->getUser();
    $this->addCodingJam092021Achievement($project, $user);
  }

  /**
   * @throws Exception
   */
  protected function addCodingJam092021Achievement(Program $program, User $user): void
  {
    $tags = $program->getTags();
    /** @var Tag $tag */
    foreach ($tags as $tag) {
      if (Tag::CODING_JAM_09_2021 === $tag->getInternalTitle()) {
        $this->achievement_manager->unlockAchievementCodingJam092021($user);
        break;
      }
    }

    if (str_contains($program->getDescription() ?? '', '#catrobatfestival2021')) {
      $coding_jam_09_2021_tag = $this->tag_repository->findOneBy(['internal_title' => Tag::CODING_JAM_09_2021]);
      if (!$program->getTags()->contains($coding_jam_09_2021_tag)) {
        $program->addTag($coding_jam_09_2021_tag);
      }
      $this->achievement_manager->unlockAchievementCodingJam092021($user);
    }
  }
}
