<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\Notification\NotificationManager;

class ReactionsApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly ProgramLikeRepository $program_like_repository,
    private readonly NotificationRepository $notification_repository,
    private readonly NotificationManager $notification_manager,
  ) {
  }

  /**
   * Add a reaction to a project.
   *
   * @return bool True if reaction was added, false if it already existed
   */
  public function addReaction(Program $project, User $user, int $type): bool
  {
    // Check if reaction already exists
    $existing_likes = $this->program_like_repository->findBy([
      'program' => $project,
      'user' => $user,
      'type' => $type,
    ]);

    if ([] !== $existing_likes) {
      return false; // Reaction already exists
    }

    $this->program_like_repository->addLike($project, $user, $type);
    $this->handleNotification($project, $user, ProgramLike::ACTION_ADD, $type);

    return true;
  }

  /**
   * Remove a reaction from a project.
   */
  public function removeReaction(Program $project, User $user, int $type): void
  {
    $this->program_like_repository->removeLike($project, $user, $type);
    $this->handleNotification($project, $user, ProgramLike::ACTION_REMOVE, $type);
  }

  /**
   * Handle notification logic for reactions.
   *
   * - Creates notification only on user's first reaction to a project
   * - Removes notification only when all user's reactions are removed
   * - Skips notification if user owns the project
   */
  private function handleNotification(Program $project, User $user, string $action, int $type): void
  {
    $project_owner = $project->getUser();

    // Don't notify if no owner or if user owns the project
    if (null === $project_owner || $project_owner === $user) {
      return;
    }

    $existing_notifications = $this->notification_repository->getLikeNotificationsForProject(
      $project,
      $project_owner,
      $user
    );

    if (ProgramLike::ACTION_ADD === $action) {
      // Only create notification if none exists (user's first reaction to this project)
      if ([] === $existing_notifications) {
        $notification = new LikeNotification($project_owner, $user, $project);
        $this->notification_manager->addNotification($notification);
      }
    } elseif (ProgramLike::ACTION_REMOVE === $action) {
      // Only remove notification if no other reaction types remain
      if (!$this->program_like_repository->areThereOtherLikeTypes($project, $user, $type)) {
        foreach ($existing_notifications as $notification) {
          $this->notification_manager->removeNotification($notification);
        }
      }
    }
  }

  /**
   * Convert reaction type name to integer constant.
   */
  public static function getTypeFromName(string $type_name): ?int
  {
    return match ($type_name) {
      'thumbs_up' => ProgramLike::TYPE_THUMBS_UP,
      'smile' => ProgramLike::TYPE_SMILE,
      'love' => ProgramLike::TYPE_LOVE,
      'wow' => ProgramLike::TYPE_WOW,
      default => null,
    };
  }
}
