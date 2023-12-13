<?php

namespace App\Studio;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioActivityRepository;
use App\DB\EntityRepository\Studios\StudioProgramRepository;
use App\DB\EntityRepository\Studios\StudioRepository;
use App\DB\EntityRepository\Studios\StudioUserRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use Doctrine\ORM\EntityManagerInterface;

class StudioManager
{
  public function __construct(protected EntityManagerInterface $entity_manager, protected StudioRepository $studio_repository, protected StudioActivityRepository $studio_activity_repository, protected StudioProgramRepository $studio_project_repository, protected StudioUserRepository $studio_user_repository, protected UserCommentRepository $user_comment_repository) {}

  public function createStudio(User $user, string $name, string $description, bool $is_public = true, bool $is_enabled = true, bool $allow_comments = true, string $cover_path = null): Studio
  {
    $studio = (new Studio())
      ->setName($name)
      ->setDescription($description)
      ->setIsPublic($is_public)
      ->setIsEnabled($is_enabled)
      ->setAllowComments($allow_comments)
      ->setCoverPath($cover_path)
      ->setCreatedOn(new \DateTime())
    ;

    $this->saveStudio($studio);
    $this->addAdminToStudio( $user,  $studio);

    return $studio;
  }

  protected function createActivity(User $user, Studio $studio, string $type): StudioActivity
  {
    $activity = (new StudioActivity())
      ->setStudio($studio)
      ->setType($type)
      ->setUser($user)
      ->setCreatedOn(new \DateTime())
    ;

    $this->entity_manager->persist($activity);
    $this->entity_manager->flush();

    return $activity;
  }

  protected function createStudioUser(User $user, Studio $studio, StudioActivity $activity, string $role, string $status = StudioUser::STATUS_ACTIVE): StudioUser
  {
    $studioUser = (new StudioUser())
      ->setStudio($studio)
      ->setActivity($activity)
      ->setUser($user)
      ->setRole($role)
      ->setStatus($status)
      ->setCreatedOn(new \DateTime())
    ;

    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  protected function createStudioComment(User $user, Studio $studio, StudioActivity $activity, string $text, int $parent_id): UserComment
  {
    $comment = (new UserComment())
      ->setStudio($studio)
      ->setActivity($activity)
      ->setText($text)
      ->setUser($user)
      ->setUploadDate(new \DateTime())
      ->setUsername($user->getUsername())
      ->setParentId($parent_id)
    ;

    $this->entity_manager->persist($comment);
    $this->entity_manager->flush();

    return $comment;
  }

  protected function createStudioProgram(User $user, Studio $studio, StudioActivity $activity, Program $project): StudioProgram
  {
    $studioProject = (new StudioProgram())
      ->setStudio($studio)
      ->setActivity($activity)
      ->setUser($user)
      ->setProgram($project)
      ->setCreatedOn(new \DateTime())
    ;

    $this->entity_manager->persist($studioProject);
    $this->entity_manager->flush();

    return $studioProject;
  }

    public function isStudioPublic(Studio $studio): bool
    {
        return $studio->isIsPublic();
    }

    public function isStudioEnabled(Studio $studio): bool
    {
        return $studio->isIsEnabled();
    }
    public function addUserToStudio(User $admin, Studio $studio, User $newUser, string $role = StudioUser::ROLE_MEMBER): ?StudioUser
  {
    if (($this->isUserInStudio($admin, $studio) && $this->isUserAStudioAdmin( $admin, $studio))
      && (!$this->isUserInStudio($newUser, $studio) && StudioUser::ROLE_ADMIN !== $role))
    {
        $activity = $this->createActivity($admin, $studio, StudioActivity::TYPE_USER);
        return $this->createStudioUser($newUser, $studio, $activity, $role);
    }
    else
    {
        return null;
    }


  }

    public function addAdminToStudio(User $admin, Studio $studio, string $role = StudioUser::ROLE_ADMIN): ?StudioUser
    {
        if ((!$this->isUserInStudio($admin, $studio) && StudioUser::ROLE_ADMIN == $role))
        {
            $activity = $this->createActivity($admin, $studio, StudioActivity::TYPE_USER);
            return $this->createStudioUser($admin, $studio, $activity, $role);
        }
        else
        {
            return null;
        }


    }

  public function addCommentToStudio(User $user, Studio $studio, string $comment_text, int $parent_id = 0): ?UserComment
  {
    if (!$this->isUserInStudio($user, $studio)) {
      return null;
    }

    $activity = $this->createActivity($user, $studio, StudioActivity::TYPE_COMMENT);

    return $this->createStudioComment($user, $studio, $activity, $comment_text, $parent_id);
  }

  public function addProjectToStudio(User $user, Studio $studio, Program $project): ?StudioProgram
  {
    if (!$this->isUserInStudio($user, $studio)) {
      return null;
    }

    $activity = $this->createActivity($user, $studio, StudioActivity::TYPE_PROJECT);

    return $this->createStudioProgram($user, $studio, $activity, $project);
  }

  public function changeStudio(User $user, Studio $studio): ?studio
  {
    if ($this->isUserAStudioAdmin($user, $studio)) {
      return $this->saveStudio($studio);
    }

    return null;
  }

  public function changeStudioUserStatus(User $admin, Studio $studio, User $user_to_change, string $status): ?StudioUser
  {
    if (!$this->isUserInStudio($admin, $studio) || !$this->isUserAStudioAdmin($admin, $studio)) {
      return null;
    }
    $studioUser = $this->findStudioUser($user_to_change, $studio);
    if (is_null($studioUser)) {
      return null;
    }
    $studioUser->setUpdatedOn(new \DateTime('now'));
    $studioUser->setStatus($status);
    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  public function changeStudioUserRole(User $admin, Studio $studio, User $user_to_change, string $role): ?StudioUser
  {
    if (!$this->isUserInStudio($admin, $studio) || !$this->isUserAStudioAdmin($admin, $studio)) {
      return null;
    }
    $studioUser = $this->findStudioUser($user_to_change, $studio);
    if (is_null($studioUser)) {
      return null;
    }
    $studioUser->setUpdatedOn(new \DateTime('now'));
    $studioUser->setRole($role);
    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  public function editStudioComment(User $user, int $comment_id, string $comment_text): ?UserComment
  {
    $studioComment = $this->findStudioCommentById($comment_id);
    if ($this->isUserInStudio($user, $studioComment->getStudio()) && $user->getUsername() === $studioComment->getUser()->getUserIdentifier()) {
      $studioComment->setText($comment_text);
      $this->entity_manager->persist($studioComment);
      $this->entity_manager->flush();

      return $studioComment;
    }

    return null;
  }

  public function deleteUserFromStudio(User $admin, Studio $studio, User $user_to_remove): void
  {
    $studio_user = $this->findStudioUser($user_to_remove, $studio);
    if (($this->isUserAStudioAdmin($admin, $studio) || $admin === $user_to_remove) && !is_null($studio_user)) {
      $this->entity_manager->remove($studio_user);
      $this->entity_manager->flush();
    }
  }

  public function deleteCommentFromStudio(User $user, int $comment_id): void
  {
    $comment = $this->findStudioCommentById($comment_id);
    if ($this->isUserInStudio($user, $comment->getStudio()) && ($user->getUsername() === $comment->getUser()->getUserIdentifier() || $this->isUserAStudioAdmin($user, $comment->getStudio()))) {
      $replies = $this->user_comment_repository->findCommentReplies($comment_id);
      foreach ($replies as $reply) {
        $this->entity_manager->remove($reply);
      }
      $this->entity_manager->remove($comment);
      $this->entity_manager->flush();
    }
  }

  public function deleteProjectFromStudio(User $user, Studio $studio, Program $program): void
  {
    $studio_project = $this->findStudioProject($studio, $program);

    if ($this->isUserAStudioAdmin($user, $studio) || ($this->isUserInStudio($user, $studio) && $program->getUser() === $user)) {
      $this->entity_manager->remove($studio_project);
      $this->entity_manager->flush();
    }
  }

  public function deleteStudio(Studio $studio, User $user): void
  {
    if ($this->isUserAStudioAdmin($user, $studio)) {
      $this->entity_manager->remove($studio);
      $this->entity_manager->flush();
    }
  }

  protected function deleteActivity(StudioActivity $activity): void
  {
    $this->entity_manager->remove($activity);
    $this->entity_manager->flush();
  }

  public function findStudioById(string $studio_id): ?Studio
  {
    return $this->studio_repository->findStudioById($studio_id);
  }

  public function findStudioByName(string $studio_name): ?Studio
  {
    return $this->studio_repository->findStudioByName($studio_name);
  }

  public function findAllStudiosWithUsersAndProjectsCount(): array
  {
    return $this->studio_repository->findAllStudiosWithUsersAndProjectsCount();
  }

  public function findAllStudioActivities(Studio $studio): array
  {
    return $this->studio_activity_repository->findAllStudioActivities($studio);
  }

  public function findAllStudioActivitiesCombined(Studio $studio): array
  {
    return $this->studio_activity_repository->findAllStudioActivitiesCombined($studio);
  }

  public function countStudioActivities(?Studio $studio): int
  {
    return $this->studio_activity_repository->countStudioActivities($studio);
  }
  public function getStudioAdmin(Studio $studio): ?StudioUser
  {
      return $this->studio_user_repository->findStudioAdmin($studio);
  }
  public function findStudioUser(?User $user, Studio $studio): ?StudioUser
  {
    return $this->studio_user_repository->findStudioUser($user, $studio);
  }

  public function countStudioUsers(?Studio $studio): int
  {
    return $this->studio_user_repository->countStudioUsers($studio);
  }

  public function getStudioUserRole(?User $user, Studio $studio): ?string
  {
    $studio_user = $this->findStudioUser($user, $studio);
    if (is_null($studio_user)) {
      return null;
    }

    return $studio_user->getRole();
  }

  public function getStudioUserStatus(User $user, Studio $studio): ?string
  {
    $studioUser = $this->findStudioUser($user, $studio);
    if (is_null($studioUser)) {
      return null;
    }

    return $studioUser->getStatus();
  }

  public function findAllStudioUsers(?Studio $studio): array
  {
    return $this->studio_user_repository->findAllStudioUsers($studio);
  }

  public function findAllStudioComments(Studio $studio): array
  {
    return $this->user_comment_repository->findAllStudioComments($studio);
  }

  public function countStudioComments(?Studio $studio): int
  {
    return $this->user_comment_repository->countStudioComments($studio);
  }

  public function findStudioCommentById(int $comment_id): ?UserComment
  {
    return $this->user_comment_repository->findStudioCommentById($comment_id);
  }

  public function findCommentReplies(int $comment_id): array
  {
    return $this->user_comment_repository->findCommentReplies($comment_id);
  }

  public function countCommentReplies(int $comment_id): int
  {
    return $this->user_comment_repository->countCommentReplies($comment_id);
  }

  public function findAllStudioProjects(Studio $studio): array
  {
    return $this->studio_project_repository->findAllStudioProjects($studio);
  }

  public function findStudioProject(Studio $studio, Program $program): ?StudioProgram
  {
    return $this->studio_project_repository->findStudioProject($studio, $program);
  }

  public function countStudioProjects(?Studio $studio): int
  {
    return $this->studio_project_repository->countStudioProjects($studio);
  }

  public function countStudioUserProjects(?Studio $studio, ?User $user): int
  {
    return $this->studio_project_repository->countStudioUserProjects($studio, $user);
  }

  public function isUserInStudio(User $user, Studio $studio): bool
  {
    return !is_null($this->findStudioUser($user, $studio));
  }

  public function isUserAStudioAdmin(User $user, Studio $studio): bool
  {
    return StudioUser::ROLE_ADMIN === $this->getStudioUserRole($user, $studio);
  }

  protected function saveStudio(Studio $studio): Studio
  {
    $this->entity_manager->persist($studio);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($studio);

    return $studio;
  }
}
