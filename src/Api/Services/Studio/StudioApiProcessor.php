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
use App\Moderation\TextSanitizer;
use App\Project\ProjectManager;
use App\Studio\StudioManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudioApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly StudioManager $studio_manager,
    private readonly ProjectManager $project_manager,
    private readonly TextSanitizer $textSanitizer,
  ) {
  }

  public function create(User $user, string $name, string $description, bool $is_public, bool $enable_comments, ?UploadedFile $image_file): Studio
  {
    return $this->studio_manager->createStudio(
      $user,
      $this->textSanitizer->sanitize($name) ?? '',
      $this->textSanitizer->sanitize($description) ?? '',
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
      $this->textSanitizer->sanitize($name),
      $this->textSanitizer->sanitize($description),
      $is_public,
      $enable_comments,
      $image_file
    );
  }

  public function deleteStudio(Studio $studio, User $user): void
  {
    $this->studio_manager->deleteStudio($studio, $user);
  }

  /**
   * @return bool true if joined successfully, false if admin not found
   */
  public function joinStudio(User $user, Studio $studio): bool
  {
    if ($studio->isIsPublic()) {
      $admin = $this->studio_manager->getStudioAdmin($studio);
      if (!$admin instanceof StudioUser) {
        return false;
      }
      $this->studio_manager->addUserToStudio($admin->getUser(), $studio, $user);
    } else {
      $this->studio_manager->setJoinRequest($user, $studio, StudioJoinRequest::STATUS_PENDING);
    }

    return true;
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
   * @param string[] $project_ids
   *
   * @return array{added: string[], failed: array<array{project_id: string, reason: string}>}
   */
  public function addProjects(User $user, Studio $studio, array $project_ids): array
  {
    $added = [];
    $failed = [];

    foreach ($project_ids as $project_id) {
      $result = $this->addProject($user, $studio, $project_id);
      if ('ok' === $result) {
        $added[] = $project_id;
      } else {
        $failed[] = ['project_id' => $project_id, 'reason' => $result];
      }
    }

    return ['added' => $added, 'failed' => $failed];
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
    $isOwner = $project->getUser()?->getId() === $user->getId();
    if (!$isAdmin && !$isOwner) {
      return 'forbidden';
    }

    $this->studio_manager->deleteProjectFromStudio($user, $studio, $project);

    return 'ok';
  }

  public function promoteMember(User $admin, Studio $studio, User $target): ?StudioUser
  {
    return $this->studio_manager->changeStudioUserRole($admin, $studio, $target, StudioUser::ROLE_ADMIN);
  }

  public function demoteMember(User $admin, Studio $studio, User $target): ?StudioUser
  {
    if ($this->studio_manager->countStudioAdmins($studio) <= 1) {
      return null;
    }

    return $this->studio_manager->changeStudioUserRole($admin, $studio, $target, StudioUser::ROLE_MEMBER);
  }

  public function banMember(User $admin, Studio $studio, User $target): ?StudioUser
  {
    $result = $this->studio_manager->changeStudioUserStatus($admin, $studio, $target, StudioUser::STATUS_BANNED);
    if ($result instanceof StudioUser && StudioUser::STATUS_BANNED === $result->getStatus()) {
      return $result;
    }

    return null;
  }

  public function addComment(User $user, Studio $studio, string $text, int $parent_id): ?UserComment
  {
    return $this->studio_manager->addCommentToStudio($user, $studio, $this->textSanitizer->sanitize($text) ?? '', $parent_id);
  }

  public function deleteComment(User $user, int $comment_id): void
  {
    $this->studio_manager->deleteCommentFromStudio($user, $comment_id);
  }

  public function acceptJoinRequest(User $admin, Studio $studio, StudioJoinRequest $joinRequest): void
  {
    $requestUser = $joinRequest->getUser();
    if (null === $requestUser) {
      return;
    }

    $this->studio_manager->updateJoinRequests($joinRequest, '1', $requestUser, $admin, $studio);
  }

  public function declineJoinRequest(StudioJoinRequest $joinRequest): void
  {
    $requestUser = $joinRequest->getUser();
    $requestStudio = $joinRequest->getStudio();
    if (null === $requestUser || null === $requestStudio) {
      return;
    }

    $this->studio_manager->updateJoinRequests($joinRequest, '0', $requestUser, $requestUser, $requestStudio);
  }
}
