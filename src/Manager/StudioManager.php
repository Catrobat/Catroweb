<?php

namespace App\Manager;

use App\Entity\Program;
use App\Entity\Studio;
use App\Entity\StudioActivity;
use App\Entity\StudioProgram;
use App\Entity\StudioUser;
use App\Entity\User;
use App\Entity\UserComment;
use App\Repository\Studios\StudioActivityRepository;
use App\Repository\Studios\StudioProgramRepository;
use App\Repository\Studios\StudioRepository;
use App\Repository\Studios\StudioUserRepository;
use App\Repository\UserCommentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class StudioManager
{
  protected EntityManagerInterface $entity_manager;
  protected StudioRepository $studio_repository;
  protected StudioActivityRepository $studio_activity_repository;
  protected StudioProgramRepository $studio_project_repository;
  protected StudioUserRepository $studio_user_repository;
  protected UserCommentRepository $user_comment_repository;

  public function __construct(EntityManagerInterface $entity_manager, StudioRepository $studio_repository,
                              StudioActivityRepository $studio_activity_repository, StudioProgramRepository $studio_project_repository,
                              StudioUserRepository $studio_user_repository, UserCommentRepository $user_comment_repository)
  {
    $this->entity_manager = $entity_manager;
    $this->studio_repository = $studio_repository;
    $this->studio_activity_repository = $studio_activity_repository;
    $this->studio_project_repository = $studio_project_repository;
    $this->studio_user_repository = $studio_user_repository;
    $this->user_comment_repository = $user_comment_repository;
  }

  public function createStudio(User $user, string $studioName, string $studioDescription, bool $is_public = true, bool $is_enabled = true, bool $allow_comments = true, string $cover_path = null): ?Studio
  {
    $studio = new Studio();
    $studio->setName($studioName);
    $studio->setDescription($studioDescription);
    $studio->setIsPublic($is_public);
    $studio->setIsEnabled($is_enabled);
    $studio->setAllowComments($allow_comments);
    $studio->setCoverPath($cover_path);
    $studio->setCreatedOn(new DateTime());
    $this->entity_manager->persist($studio);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($studio);
    $this->addUserToStudio($user, $studio, $user, StudioUser::ROLE_ADMIN);

    return $studio;
  }

  public function editStudio(User $user, Studio $studio): studio
  {
    if (StudioUser::ROLE_ADMIN === $this->getStudioUserRole($user, $studio)) {
      $this->entity_manager->persist($studio);
      $this->entity_manager->flush();
    }

    return $studio;
  }

  public function findStudioById(string $studio_id): ?Studio
  {
    return $this->studio_repository->findStudioById($studio_id);
  }

  public function findAllStudiosWithUsersAndProjectsCount(): array
  {
    return $this->studio_repository->findAllStudiosWithUsersAndProjectsCount();
  }

  public function deleteStudio(Studio $studio, User $user): void
  {
    if (StudioUser::ROLE_ADMIN === $this->getStudioUserRole($user, $studio)) {
      $this->entity_manager->remove($studio);
      $this->entity_manager->flush();
    }
  }

  protected function createActivity(User $user, Studio $studio, string $type): ?StudioActivity
  {
    $activity = new StudioActivity();
    $activity->setStudio($studio);
    $activity->setType($type);
    $activity->setUser($user);
    $activity->setCreatedOn(new DateTime());
    $this->entity_manager->persist($activity);
    $this->entity_manager->flush();

    return $activity;
  }

  protected function deleteActivity(StudioActivity $activity): void
  {
    $this->entity_manager->remove($activity);
    $this->entity_manager->flush();
  }

  public function findStudioActivitiesCount(?Studio $studio): int
  {
    return $this->studio_activity_repository->findStudioActivitiesCount($studio);
  }

  public function addUserToStudio(User $admin, Studio $studio, User $newUser, string $userRole = StudioUser::ROLE_MEMBER): ?StudioUser
  {
    if (($this->isUserInStudio($admin, $studio) && StudioUser::ROLE_ADMIN !== $this->getStudioUserRole($admin, $studio))
      || (!$this->isUserInStudio($admin, $studio) && StudioUser::ROLE_ADMIN !== $userRole)) {
      return null;
    }
    $activity = $this->createActivity($admin, $studio, StudioActivity::TYPE_USER);
    if (is_null($activity)) {
      return null;
    }
    $studioUser = new StudioUser();
    $studioUser->setStudio($studio);
    $studioUser->setActivity($activity);
    $studioUser->setUser($newUser);
    $studioUser->setRole($userRole);
    $studioUser->setStatus(StudioUser::STATUS_ACTIVE);
    $studioUser->setCreatedOn(new DateTime());
    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  public function findStudioUser(?User $user, Studio $studio): ?StudioUser
  {
    return $this->studio_user_repository->findStudioUser($user, $studio);
  }

  public function findStudioUsersCount(?Studio $studio): int
  {
    return $this->studio_user_repository->findStudioUsersCount($studio);
  }

  public function getStudioUserRole(?User $user, Studio $studio): ?string
  {
    $studioUser = $this->findStudioUser($user, $studio);
    if (is_null($studioUser)) {
      return null;
    }

    return $studioUser->getRole();
  }

  public function getStudioUserStatus(User $user, Studio $studio): ?string
  {
    $studioUser = $this->findStudioUser($user, $studio);
    if (is_null($studioUser)) {
      return null;
    }

    return $studioUser->getStatus();
  }

  public function isUserInStudio(User $user, Studio $studio): bool
  {
    return (is_null($this->findStudioUser($user, $studio))) ? false : true;
  }

  public function changeStudioUserStatus(User $admin, Studio $studio, User $user, string $status): ?StudioUser
  {
    if (!$this->isUserInStudio($admin, $studio) || StudioUser::ROLE_ADMIN !== $this->getStudioUserRole($admin, $studio)) {
      return null;
    }
    $studioUser = $this->findStudioUser($user, $studio);
    if (is_null($studioUser)) {
      return null;
    }
    $studioUser->setStatus($status);
    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  public function changeStudioUserRole(User $admin, Studio $studio, User $user, string $role): ?StudioUser
  {
    if (!$this->isUserInStudio($admin, $studio) || StudioUser::ROLE_ADMIN !== $this->getStudioUserRole($admin, $studio)) {
      return null;
    }
    $studioUser = $this->findStudioUser($user, $studio);
    if (is_null($studioUser)) {
      return null;
    }
    $studioUser->setRole($role);
    $this->entity_manager->persist($studioUser);
    $this->entity_manager->flush();

    return $studioUser;
  }

  public function deleteUserFromStudio(User $admin, Studio $studio, User $user): void
  {
    $studioUser = $this->findStudioUser($user, $studio);
    if (StudioUser::ROLE_ADMIN !== $this->getStudioUserRole($admin, $studio) || is_null($studioUser)) {
      return;
    }
    $this->deleteActivity($studioUser->getActivity());
  }

  public function addCommentToStudio(User $user, Studio $studio, string $comment_text, int $parent_id = 0): ?UserComment
  {
    if (!$this->isUserInStudio($user, $studio)) {
      return null;
    }
    $activity = $this->createActivity($user, $studio, StudioActivity::TYPE_COMMENT);
    if (is_null($activity)) {
      return null;
    }
    $studioComment = new UserComment();
    $studioComment->setStudio($this->findStudioById($studio->getId()));
    $studioComment->setActivity($activity);
    $studioComment->setText($comment_text);
    $studioComment->setUser($user);
    $studioComment->setUploadDate(new DateTime());
    $studioComment->setUsername($user->getUsername());
    $studioComment->setIsReported(false);
    $studioComment->setParentId(intval($parent_id));
    $this->entity_manager->persist($studioComment);
    $this->entity_manager->flush();

    return $studioComment;
  }

  public function editStudioComment(User $user, int $comment_id, string $comment_text): ?UserComment
  {
    $studioComment = $this->findStudioCommentById($comment_id);
    if (!$this->isUserInStudio($user, $studioComment->getStudio()) || $user->getId() !== $studioComment->getUser()->getId()) {
      return null;
    }
    $studioComment->setText($comment_text);
    $this->entity_manager->persist($studioComment);
    $this->entity_manager->flush();

    return $studioComment;
  }

  public function findAllStudioComments(Studio $studio): array
  {
    return $this->user_comment_repository->findAllStudioComments($studio);
  }

  public function findStudioCommentById(int $comment_id): ?UserComment
  {
    return $this->user_comment_repository->findStudioCommentById($comment_id);
  }

  public function findStudioCommentsCount(?Studio $studio): int
  {
    return $this->user_comment_repository->findStudioCommentsCount($studio);
  }

  public function findCommentReplies(int $comment_id): array
  {
    return $this->user_comment_repository->findCommentReplies($comment_id);
  }

  public function findCommentRepliesCount(int $comment_id): int
  {
    return $this->user_comment_repository->findCommentRepliesCount($comment_id);
  }

  public function deleteCommentFromStudio(User $user, int $comment_id): void
  {
    $comment = $this->findStudioCommentById($comment_id);
    if ($this->isUserInStudio($user, $comment->getStudio()) && ($user->getId() === $comment->getUser()->getId() || StudioUser::ROLE_ADMIN === $this->getStudioUserRole($user, $comment->getStudio()))) {
      foreach ($this->user_comment_repository->findCommentReplies($comment_id) as $reply) {
        $this->deleteActivity($reply->getActivity());
      }
      $this->deleteActivity($comment->getActivity());
    }
  }

  public function addProjectToStudio(User $user, Studio $studio, Program $project): ?StudioProgram
  {
    if (!$this->isUserInStudio($user, $studio)) {
      return null;
    }
    $activity = $this->createActivity($user, $studio, StudioActivity::TYPE_PROJECT);
    if (is_null($activity)) {
      return null;
    }
    $studioProject = new StudioProgram();
    $studioProject->setStudio($studio);
    $studioProject->setActivity($activity);
    $studioProject->setUser($user);
    $studioProject->setProgram($project);
    $studioProject->setCreatedOn(new DateTime());
    $this->entity_manager->persist($studioProject);
    $this->entity_manager->flush();

    return $studioProject;
  }

  public function findAllStudioProjects(Studio $studio): array
  {
    return $this->studio_project_repository->findAllStudioProjects($studio);
  }

  public function findStudioProject(Studio $studio, Program $program): ?StudioProgram
  {
    return $this->studio_project_repository->findStudioProject($studio, $program);
  }

  public function findStudioProjectsCount(?Studio $studio): int
  {
    return $this->studio_project_repository->findStudioProjectsCount($studio);
  }

  public function deleteProjectFromStudio(User $user, Studio $studio, Program $program): void
  {
    $studioProject = $this->findStudioProject($studio, $program);
    if (StudioUser::ROLE_ADMIN === $this->getStudioUserRole($user, $studioProject->getStudio()) || ($this->isUserInStudio($user, $studio) && $program->getUser() === $user)) {
      $this->deleteActivity($studioProject->getActivity());
    }
  }
}
