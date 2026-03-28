<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Studio\StudioManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudioApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly StudioManager $studio_manager,
    private readonly ProjectManager $project_manager,
  ) {
  }

  public function create(User $user, string $name, string $description, bool $is_public, bool $enable_comments, ?UploadedFile $image_file): Studio
  {
    return $this->studio_manager->createStudio(
      $user,
      $name,
      $description,
      $is_public,
      true,
      $enable_comments,
      $image_file
    );
  }

  public function update(Studio $studio, ?string $name, ?string $description, ?bool $is_public, ?bool $enable_comments, ?UploadedFile $image_file): Studio
  {
    return $this->studio_manager->updateStudio(
      $studio,
      $name,
      $description,
      $is_public,
      $enable_comments,
      $image_file
    );
  }

  public function deleteStudio(Studio $studio, User $user): void
  {
    $this->studio_manager->deleteStudio($studio, $user);
  }

  public function joinStudio(User $user, Studio $studio): void
  {
    if ($studio->isIsPublic()) {
      $admin = $this->studio_manager->getStudioAdmin($studio);
      if ($admin instanceof StudioUser) {
        $this->studio_manager->addUserToStudio($admin->getUser(), $studio, $user);
      }
    } else {
      $this->studio_manager->setJoinRequest($user, $studio, StudioJoinRequest::STATUS_PENDING);
    }
  }

  public function leaveStudio(User $user, Studio $studio): void
  {
    $this->studio_manager->deleteUserFromStudio($user, $studio, $user);

    $joinRequest = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
    if (null !== $joinRequest) {
      $this->studio_manager->removeJoinRequest($joinRequest);
    }
  }

  /**
   * @return string 'ok'|'not_found'|'conflict'
   */
  public function addProject(User $user, Studio $studio, string $project_id): string
  {
    $project = $this->project_manager->find($project_id);
    if (!$project instanceof Program) {
      return 'not_found';
    }

    $existing = $this->studio_manager->findStudioProject($studio, $project);
    if (null !== $existing) {
      return 'conflict';
    }

    $this->studio_manager->addProjectToStudio($user, $studio, $project);

    return 'ok';
  }

  /**
   * @return string 'ok'|'not_found'|'forbidden'
   */
  public function removeProject(User $user, Studio $studio, string $project_id): string
  {
    $project = $this->project_manager->find($project_id);
    if (!$project instanceof Program) {
      return 'not_found';
    }

    $studioProject = $this->studio_manager->findStudioProject($studio, $project);
    if (null === $studioProject) {
      return 'not_found';
    }

    $isAdmin = $this->studio_manager->isUserAStudioAdmin($user, $studio);
    $isOwner = $project->getUser() === $user;
    if (!$isAdmin && !$isOwner) {
      return 'forbidden';
    }

    $this->studio_manager->deleteProjectFromStudio($user, $studio, $project);

    return 'ok';
  }

  public function addComment(User $user, Studio $studio, string $text, int $parent_id): ?UserComment
  {
    return $this->studio_manager->addCommentToStudio($user, $studio, $text, $parent_id);
  }
}
