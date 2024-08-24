<?php

declare(strict_types=1);

namespace App\Application\Controller\Studio;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use App\Studio\StudioManager;
use App\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudioController extends AbstractController
{
  public function __construct(protected StudioManager $studio_manager, protected UserManager $user_manager, protected ProjectManager $program_manager, protected ScreenshotRepository $screenshot_repository, protected TranslatorInterface $translator, protected ParameterBagInterface $parameter_bag)
  {
  }

  #[Route(path: '/studios', name: 'studios_overview', methods: ['GET'])]
  public function studiosOverview(): Response
  {
    $studios = $this->studio_manager->findAllStudiosWithUsersAndProjectsCount();
    /** @var User|null $user */
    $user = $this->getUser();
    $counter = count($studios);
    for ($i = 0; $i < $counter; ++$i) {
      $studio = $this->studio_manager->findStudioById($studios[$i]['id']);
      $studios[$i]['is_joined'] = !is_null($user) && $this->studio_manager->isUserInStudio($user, $studio);
      $studios[$i]['status'] = 'false';
      if (!is_null($user)) {
        $status = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
        if (!is_null($status)) {
          $studios[$i]['status'] = $status->getStatus();
        }
      }
    }

    return $this->render('Studio/Studios.html.twig', [
      'studios' => $studios,
      'user_name' => is_null($user) ? '' : $user->getUserIdentifier(),
    ]);
  }

  /**
   * @internal route for now
   */
  #[Route(path: '/studio/new', name: 'studio_new', methods: ['GET'])]
  public function studioNew(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    return $this->render('Studio/CreatePage.html.twig', [
      'user_name' => is_null($user) ? '' : $user->getUserIdentifier(),
    ]);
  }

  #[Route(path: '/studio/{id}', name: 'studio_details', methods: ['GET'])]
  public function studioDetails(Request $request): Response
  {
    $studio = $this->studio_manager->findStudioById(trim((string) $request->attributes->get('id')));
    if (is_null($studio)) {
      throw $this->createNotFoundException('Unable to find this studio');
    }

    /** @var User|null $user */
    $user = $this->getUser();
    $user_role = $this->studio_manager->getStudioUserRole($user, $studio);
    $members_count = $this->studio_manager->countStudioUsers($studio);
    $activities_count = $this->studio_manager->countStudioActivities($studio);
    $projects = $this->studio_manager->findAllStudioProjects($studio);
    $projects_count = $this->studio_manager->countStudioProjects($studio);
    $comments_count = $this->studio_manager->countStudioComments($studio);
    $comments = $this->studio_manager->findAllStudioComments($studio);
    $statusPublicStudio = !is_null($user) && $this->studio_manager->isUserInStudio($user, $studio);
    $statusPrivateStudio = 'false';
    $user_projects = [];
    if (!is_null($user)) {
      $user_projects = $this->studio_manager->getUserProjects($user);
      $currentStatus = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
      if (!is_null($currentStatus)) {
        $statusPrivateStudio = $currentStatus->getStatus();
      }
    }

    return $this->render('Studio/DetailsPage.html.twig', [
      'status_public' => $statusPublicStudio,
      'status_private' => $statusPrivateStudio,
      'studio' => $studio,
      'user_name' => is_null($this->getUser()) ? '' : $this->getUser()->getUserIdentifier(),
      'user_role' => $user_role,
      'members_count' => $members_count,
      'activities_count' => $activities_count,
      'projects_count' => $projects_count,
      'projects' => $this->getStudioProjectsListWithImg($projects),
      'comments_count' => $comments_count,
      'comments' => $this->getStudioCommentsListWithAvatar($comments),
      'user_projects' => $this->getUserProjectsListImgAndCheckIfStudioProject($user_projects, $studio),
      'pending_join_requests' => $this->studio_manager->findPendingJoinRequests($studio),
      'declined_join_requests' => $this->studio_manager->findDeclinedJoinRequests($studio),
      'approved_join_requests' => $this->studio_manager->findApprovedJoinRequests($studio),
    ]);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/studio', name: 'studio_create', methods: ['POST'])]
  public function createStudio(Request $request): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      throw $this->createAccessDeniedException();
    }

    $is_enabled = (bool) $request->request->get('is_enabled', false);
    $is_public = (bool) $request->request->get('is_public', false);
    $allow_comments = (bool) $request->request->get('allow_comments', false);
    $name = trim((string) $request->request->get('name', ''));
    $description = trim((string) $request->request->get('description', ''));
    $headerImg = $request->files->get('image');
    if ('' === $name) {
      return new JsonResponse(['message' => 'arguments invalid'], Response::HTTP_BAD_REQUEST);
    }

    $existingStudio = $this->studio_manager->findStudioByName($name);
    if ($existingStudio instanceof Studio) {
      return new JsonResponse(['message' => 'studio name is already taken'], Response::HTTP_CONFLICT);
    }

    $studio = $this->studio_manager->createStudio($user, $name, $description, $is_public, $allow_comments, $is_enabled);

    if (is_null($headerImg)) {
      return new JsonResponse(['message' => sprintf('"%s" successfully created the studio', $user->getUsername())], Response::HTTP_OK);
    }

    $newPath = 'images/default/';
    $coverPath = $this->parameter_bag->get('catrobat.resources.dir').$newPath;
    $coverName = (new \DateTime())->getTimestamp().$headerImg->getClientOriginalName();
    if (!file_exists($coverPath)) {
      $fs = new Filesystem();
      $fs->mkdir($coverPath);
    }

    $headerImg->move($coverPath, $coverName);
    $pathToSave = '/'.$newPath.$coverName;
    $studio->setCoverPath('resources'.$pathToSave);
    /** @var User|null $user */
    $user = $this->getUser();
    $this->studio_manager->changeStudio($user, $studio);

    return new JsonResponse(['message' => sprintf('"%s" successfully created the studio', $user->getUsername())], Response::HTTP_OK);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/{id}/join', name: 'studio_join', methods: ['POST'])]
  public function joinStudio(string $id): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      throw $this->createAccessDeniedException();
    }

    /** @var Studio|null $studio */
    $studio = $this->studio_manager->findStudioById($id);
    if (null == $studio) {
      return new JsonResponse(['message' => 'studio not found'], Response::HTTP_NOT_FOUND);
    }

    $admin = $this->studio_manager->getStudioAdmin($studio);
    if (!$admin instanceof StudioUser) {
      return new JsonResponse(['message' => 'No admin found for the studio'], Response::HTTP_NOT_FOUND);
    }

    $admin = $admin->getUser();
    if ($this->studio_manager->isStudioPublic($studio)) {
      $this->studio_manager->addUserToStudio($admin, $studio, $user);
    }

    /* add to join list so admin can accept/decline or so?  for private studios */
    if (!$this->studio_manager->isStudioPublic($studio)) {
      $this->studio_manager->setJoinRequest($user, $studio, StudioJoinRequest::STATUS_PENDING);
      /* admin must get inform that a user wants to join the private studio */
    }

    return new JsonResponse(['message' => sprintf('"%s" successfully entered the group', $user->getUsername())], Response::HTTP_OK);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/{id}/leave', name: 'studio_leave', methods: ['POST'])]
  public function leaveStudio(string $id): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      throw $this->createAccessDeniedException();
    }

    $studio = $this->studio_manager->findStudioById($id);
    if (!$studio instanceof Studio) {
      return new JsonResponse(['message' => 'studio not found'], Response::HTTP_NOT_FOUND);
    }

    $this->studio_manager->isUserAStudioAdmin($user, $studio);
    $this->studio_manager->deleteUserFromStudio($user, $studio, $user);

    $joinRequest = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
    if (!is_null($joinRequest)) {
      $this->studio_manager->removeJoinRequest($joinRequest);
    }

    return new JsonResponse(['message' => sprintf('"%s" successfully left the group', $user->getUsername())], Response::HTTP_OK);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/members/list', name: 'studio_members_list', methods: ['GET'])]
  public function loadStudioMembersList(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    $studio = $this->studio_manager->findStudioById(trim((string) $request->query->get('studio_id')));
    if (!is_null($studio)) {
      $this->redirectToRoute('index');
    }

    $is_studio_admin = StudioUser::ROLE_ADMIN === $this->studio_manager->getStudioUserRole($user, $studio);
    $members = $this->studio_manager->findAllStudioUsers($studio);
    $projects_per_member = [];
    /** @var StudioUser $member */
    foreach ($members as $member) {
      $projects_per_member[$member->getID()] = $this->studio_manager->countStudioUserProjects($member->getStudio(), $member->getUser());
    }

    return $this->render('Studio/members_list.html.twig', [
      'is_studio_admin' => $is_studio_admin,
      'members' => $members,
      'projects_per_member' => $projects_per_member,
    ]);
  }

  /**
   * @internal route only
   */
  #[Route('/studio/{id}/report', name: 'studio_report', methods: ['POST'])]
  public function studioReport(int $id): Response
  {
    // Your logic for handling studio report goes here

    return new JsonResponse(['message' => 'Studio reported successfully']);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/member/promote', name: 'studio_promote_member', methods: ['PUT'])]
  public function promoteMemberToAdmin(Request $request): Response
  {
    $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $studio = $this->studio_manager->findStudioById($payload['studio_id']);
    /** @var User|null $user */
    $user = $this->user_manager->findOneBy(['id' => $payload['user_id']]);
    if (is_null($studio) || is_null($user)) {
      return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    $studio_user = $this->studio_manager->findStudioUser($user, $studio);
    if (is_null($studio_user)) {
      return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /** @var User|null $logged_in_user */
    $logged_in_user = $this->getUser();
    $studio_user = $this->studio_manager->changeStudioUserRole($logged_in_user, $studio, $user, StudioUser::ROLE_ADMIN);
    if (is_null($studio_user)) {
      return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/member/ban', name: 'studio_ban_user', methods: ['PUT'])]
  public function banUserFromStudio(Request $request): Response
  {
    /** @var User|null $logged_in_user */
    $logged_in_user = $this->getUser();
    $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $studio = $this->studio_manager->findStudioById($payload['studio_id']);
    /** @var User|null $user */
    $user = $this->user_manager->findOneBy(['id' => $payload['user_id']]);
    if (is_null($studio) || is_null($user)) {
      return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    $studio_user = $this->studio_manager->findStudioUser($user, $studio);
    if (is_null($studio_user)) {
      return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    $studio_user = $this->studio_manager->changeStudioUserStatus($logged_in_user, $studio, $user, StudioUser::STATUS_BANNED);
    if (!is_null($studio_user) && StudioUser::STATUS_BANNED === $studio_user->getStatus()) {
      return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    return new JsonResponse(Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @internal route only
   */
  #[Route(path: '/studio/activities/list', name: 'studio_activities_list', methods: ['GET'])]
  public function loadStudioActivitiesList(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    $studio = $this->studio_manager->findStudioById(trim((string) $request->query->get('studio_id')));
    if (is_null($studio)) {
      $this->redirectToRoute('index');
    }

    $is_studio_admin = StudioUser::ROLE_ADMIN === $this->studio_manager->getStudioUserRole($user, $studio);
    $activities = $this->studio_manager->findAllStudioActivitiesCombined($studio);

    return $this->render('Studio/ActivityListPage.html.twig', [
      'is_studio_admin' => $is_studio_admin,
      'activities' => $activities,
    ]);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/removeStudioProject/', name: 'remove_studio_project', methods: ['POST'])]
  public function removeProjectFromStudio(Request $request): JsonResponse
  {
    $project = $this->program_manager->find(trim((string) $request->request->get('projectID')));
    $studio = $this->studio_manager->findStudioById(trim((string) $request->request->get('studioID')));
    if (is_null($project) || is_null($studio)) {
      return new JsonResponse(Response::HTTP_NOT_FOUND);
    }

    /** @var User|null $user */
    $user = $this->getUser();
    $this->studio_manager->deleteProjectFromStudio($user, $studio, $project);
    $projects_count = ' ('.$this->studio_manager->countStudioProjects($studio).')';
    $activities_count = $this->studio_manager->countStudioActivities($studio);
    if (is_null($this->studio_manager->findStudioProject($studio, $project))) {
      return new JsonResponse(['projects_count' => $projects_count, 'activities_count' => $activities_count], Response::HTTP_OK);
    }

    return new JsonResponse([], Response::HTTP_NOT_FOUND);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/removeStudioComment/', name: 'remove_studio_comment', methods: ['POST'])]
  public function removeCommentFromStudio(Request $request): JsonResponse
  {
    $comment_id = $request->request->getInt('commentID');
    $isReply = trim((string) $request->request->get('isReply'));
    $parent_id = $request->request->getInt('parentID');
    $studio = $this->studio_manager->findStudioById(trim((string) $request->request->get('studioID')));
    if (!$comment_id || is_null($studio)) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }

    $replies_count = null;
    /** @var User|null $user */
    $user = $this->getUser();
    $this->studio_manager->deleteCommentFromStudio($user, $comment_id);
    if ('true' === $isReply && $parent_id > 0) {
      $replies_count = $this->studio_manager->countCommentReplies($parent_id).' '.$this->translator->trans('studio.details.replies', [], 'catroweb');
    }

    $comments_count = ' ('.$this->studio_manager->countStudioComments($studio).')';
    $activities_count = $this->studio_manager->countStudioActivities($studio);
    if (is_null($this->studio_manager->findStudioCommentById($comment_id))) {
      return new JsonResponse(['comments_count' => $comments_count, 'activities_count' => $activities_count,
        'replies_count' => $replies_count, ], Response::HTTP_OK);
    }

    return new JsonResponse([], Response::HTTP_NOT_FOUND);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/postCommentToStudio/', name: 'post_studio_comment', methods: ['POST'])]
  public function postComment(Request $request): JsonResponse
  {
    $isReply = 'true' == $request->request->get('isReply') && $request->request->getInt('parentID') > 0;
    $studio = $this->studio_manager->findStudioById(trim((string) $request->request->get('studioID')));
    $comment_text = trim((string) $request->request->get('comment'));
    if ('' === $comment_text) {
      return new JsonResponse('', Response::HTTP_NOT_FOUND);
    }

    $replies_count = null;
    $comments_count = null;
    /** @var User|null $user */
    $user = $this->getUser();
    if ($isReply) {
      $comment = $this->studio_manager->addCommentToStudio($user, $studio, $comment_text, $request->request->getInt('parentID'));
      $replies_count = $this->studio_manager->countCommentReplies($request->request->getInt('parentID')).' '.$this->translator->trans('studio.details.replies', [], 'catroweb');
    } else {
      $comment = $this->studio_manager->addCommentToStudio($user, $studio, $comment_text);
      $comments_count = ' ('.$this->studio_manager->countStudioComments($studio).')';
    }

    $activities_count = $this->studio_manager->countStudioActivities($studio);
    $avatarSrc = $comment->getUser()->getAvatar() ?? '/images/default/avatar_default.png';
    $result = '<div class="studio-comment">';
    $result .= '<img class="comment-avatar" src="'.$avatarSrc.'" alt="Card image">';
    $result .= '<div class="comment-content">';
    $result .= '<a href="/app/user/'.$comment->getId().'">'.$comment->getUsername().'</a>';
    $result .= '<a class="comment-delete-button" data-bs-toggle="tooltip" onclick="';
    $result .= '(new Studio()).removeComment($(this), '.$comment->getId().',';
    $result .= $isReply ? 'true,'.$comment->getParentId().')">' : 'false, 0)">';
    $result .= '<i class="ms-2 material-icons text-danger">delete</i></a>';
    $result .= '<p>'.$comment->getText().'</p>';
    $result .= '<div class="comment-info">';
    $result .= '<span class="comment-time col-6">';
    $result .= '<span class="material-icons comment-info-icons">watch_later</span>'.$comment->getUploadDate()->format('Y-m-d').'</span>';
    if (!$isReply) {
      $result .= '<a class="comment-replies col-6" onclick="(new Studio()).loadReplies('.$comment->getId().')" data-bs-toggle="modal" data-bs-target="#comment-reply-modal">';
      $result .= '<span class="material-icons comment-info-icons">forum</span>';
      $result .= '<span id="info-'.$comment->getId().'">0 '.$this->translator->trans('studio.details.replies', [], 'catroweb').'</span>';
      $result .= '</div></div></div><hr class="comment-hr">';
    }

    $result .= '</div></div></div>';
    if ($comment->getText() === $comment_text) {
      return new JsonResponse(['comment' => $result, 'replies_count' => $replies_count,
        'comments_count' => $comments_count, 'activities_count' => $activities_count, ], Response::HTTP_OK);
    }

    return new JsonResponse([], Response::HTTP_NOT_FOUND);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/loadCommentReplies/', name: 'load_comment_replies', methods: ['GET'])]
  public function loadCommentReplies(Request $request): Response
  {
    $rs = '';
    $comment_id = $request->query->getInt('commentID');
    $comment = $this->studio_manager->findStudioCommentById($comment_id);
    if (is_null($comment)) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }

    $rs .= $this->getCommentsAndRepliesForAjax($comment, false);
    $replies = $this->studio_manager->findCommentReplies($comment_id);
    foreach ($replies as $reply) {
      $rs .= $this->getCommentsAndRepliesForAjax($reply, true);
    }

    /** @var User|null $user */
    $user = $this->getUser();
    if (!is_null($user) && $this->studio_manager->isUserInStudio($user, $comment->getStudio())) {
      $rs .= '<div id="add-reply" class="add-comment-section">';
      $rs .= '<input type="text" placeholder="'.$this->translator->trans('studio.details.type_something', [], 'catroweb').'">';
      $rs .= '<a href="javascript:void(0)" onclick="(new Studio()).postComment(true)">';
      $rs .= $this->translator->trans('studio.details.send_comment', [], 'catroweb');
      $rs .= '</a></div>';
    }

    return new JsonResponse($rs, Response::HTTP_OK);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/uploadStudioCover/', name: 'upload_studio_cover', methods: ['POST'])]
  public function uploadStudioCover(Request $request): Response
  {
    $studio = $this->studio_manager->findStudioById(trim((string) $request->request->get('std-id')));
    $headerImg = $request->files->get('header-img');
    if (is_null($headerImg) || is_null($studio) || is_null($this->getUser())) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }

    $newPath = 'images/Studios/';
    $coverPath = $this->parameter_bag->get('catrobat.resources.dir').$newPath;
    $coverName = (new \DateTime())->getTimestamp().$headerImg->getClientOriginalName();
    if (!file_exists($coverPath)) {
      $fs = new Filesystem();
      $fs->mkdir($coverPath);
    }

    $headerImg->move($coverPath, $coverName);
    $pathToSave = '/'.$newPath.$coverName;
    $studio->setCoverPath('resources'.$pathToSave);
    /** @var User $user */
    $user = $this->getUser();
    $this->studio_manager->changeStudio($user, $studio);

    return new JsonResponse(['new_cover' => $pathToSave], Response::HTTP_OK);
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/updateStudioDetails/', name: 'update_studio_details', methods: ['POST'])]
  public function editStudioDetails(Request $request): Response
  {
    if ($request->isMethod('POST')) {
      $studio_id = trim(strval($request->request->get('studio_id')));
      $studio = $this->studio_manager->findStudioById($studio_id);
      if (is_null($this->getUser()) || is_null($studio)) {
        return $this->redirect($request->headers->get('referer'));
      }

      $name = trim(strval($request->request->get('studio_name')));
      if (strlen($name) > 0) {
        $studio->setName($name);
      }

      $desc = trim(strval($request->request->get('studio_description')));
      if (0 !== strlen($desc)) {
        $studio->setDescription($desc);
      }

      $allow_comments = $request->request->get('allow_comments', false);
      $studio->setAllowComments(filter_var($allow_comments, FILTER_VALIDATE_BOOLEAN));

      $is_public = $request->request->get('is_public', false);
      $studio->setIsPublic(filter_var($is_public, FILTER_VALIDATE_BOOLEAN));

      $studio->setUpdatedOn(new \DateTime('now'));

      /** @var User $user */
      $user = $this->getUser();
      $formData = $request->request->all();
      $switches = $formData['switches'] ?? [];
      $approvedSwitches = $formData['approved_switches'] ?? [];
      foreach ($switches as $requestId => $switchValue) {
        $joinRequest = $this->studio_manager->findJoinRequestById($requestId);
        $this->studio_manager->updateJoinRequests($joinRequest, $switchValue, $joinRequest->getUser(), $user, $studio);
      }

      foreach ($approvedSwitches as $requestId => $switchValue) {
        $joinRequest = $this->studio_manager->findJoinRequestById($requestId);
        $this->studio_manager->updateJoinRequests($joinRequest, $switchValue, $joinRequest->getUser(), $user, $studio);
      }

      $this->studio_manager->changeStudio($user, $studio);

      return $this->redirect($request->headers->get('referer'));
    }

    return $this->redirect($request->headers->get('referer'));
  }

  /**
   * ToDo: move to capi.
   */
  #[Route(path: '/updateStudioProjects/', name: 'update_studio_projects', methods: ['POST'])]
  public function updateStudioProjects(Request $request): Response
  {
    if ($request->isMethod('POST')) {
      $studio_id = trim(strval($request->request->get('studio_id')));
      $studio = $this->studio_manager->findStudioById($studio_id);
      if (is_null($this->getUser()) || is_null($studio)) {
        return $this->redirect($request->headers->get('referer'));
      }

      /** @var User $user */
      $user = $this->getUser();
      $clickedProjectsJson = $request->request->get('projects_add');

      if (strlen($clickedProjectsJson) > 0) {
        $clickedProjects = json_decode($clickedProjectsJson, true);
        foreach ($clickedProjects as $projectId) {
          $project = $this->studio_manager->getProjectByID($projectId);
          // check for error
          $this->studio_manager->addProjectToStudio($user, $studio, $project);
        }
      }

      $clickedRemoveProjectsJson = $request->request->get('projects_remove');
      if (strlen($clickedRemoveProjectsJson) > 0) {
        $clickedRemoveProjects = json_decode($clickedRemoveProjectsJson, true);
        foreach ($clickedRemoveProjects as $projectId) {
          $project = $this->studio_manager->getProjectByID($projectId);
          $this->studio_manager->deleteProjectFromStudio($user, $studio, $project);
        }
      }
    }

    return $this->redirect($request->headers->get('referer'));
  }

  #[Route(path: '/deleteStudioProjectsAdmin/', name: 'delete_studio_projects_admin', methods: ['POST'])]
  public function deleteStudioProjects(Request $request): JsonResponse
  {
    try {
      if ($request->isMethod('POST')) {
        $studio_id = trim(strval($request->request->get('studio_id')));
        $studio = $this->studio_manager->findStudioById($studio_id);

        if (is_null($this->getUser()) || is_null($studio)) {
          return new JsonResponse(['redirect_url' => $request->headers->get('referer')]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $clickedRemoveProjectsJson = $request->request->get('projects_remove');

        if (strlen($clickedRemoveProjectsJson) > 0) {
          $clickedRemoveProjects = json_decode($clickedRemoveProjectsJson, true);

          foreach ($clickedRemoveProjects as $projectId) {
            $project = $this->studio_manager->getProjectByID($projectId);

            $this->studio_manager->deleteProjectFromStudio($user, $studio, $project);
          }

          return new JsonResponse(['redirect_url' => $request->headers->get('referer')]);
        }
      }

      return new JsonResponse(['redirect_url' => $request->headers->get('referer')]);
    } catch (\Exception) {
      return new JsonResponse(['redirect_url' => $request->headers->get('referer')]);
    }
  }

  /**
   * HELPER FUNCTIONS.
   */
  protected function getStudioProjectsListWithImg(array $projects): array
  {
    $rs = [];
    foreach ($projects as $studioProject) {
      $project = [];
      $project['id'] = $studioProject->getProgram()->getId();
      $project['name'] = $studioProject->getProgram()->getName();
      $project['thumbnail'] = $this->screenshot_repository->getThumbnailWebPath($project['id']);
      $rs[] = $project;
    }

    return $rs;
  }

  protected function getUserProjectsListImgAndCheckIfStudioProject(array $userProjects, Studio $studio): array
  {
    $rs = [];
    foreach ($userProjects as $userProject) {
      $project = [];
      $project['isStudioProject'] = !is_null($this->studio_manager->findStudioProject($studio, $userProject));
      $project['id'] = $userProject->getId();
      $project['name'] = $userProject->getName();
      $project['thumbnail'] = $this->screenshot_repository->getThumbnailWebPath($project['id']);
      $rs[] = $project;
    }

    return $rs;
  }

  protected function getStudioCommentsListWithAvatar(array $studioComments): array
  {
    $rs = [];
    $commentsObj = [];
    foreach ($studioComments as $studioComment) {
      $commentsObj['id'] = $studioComment->getId();
      $commentsObj['username'] = $studioComment->getUsername();
      $commentsObj['text'] = $studioComment->getText();
      $commentsObj['uploadDate'] = $studioComment->getUploadDate();
      $commentsObj['user'] = $studioComment->getUser();
      $commentsObj['repliesCount'] = $this->studio_manager->countCommentReplies($studioComment->getId());
      if (!is_null($studioComment->getUser()) && !is_null($studioComment->getUser()->getAvatar())) {
        $commentsObj['avatar'] = $studioComment->getUser()->getAvatar();
      } else {
        $commentsObj['avatar'] = null;
      }

      $rs[] = $commentsObj;
    }

    return $rs;
  }

  protected function getCommentsAndRepliesForAjax(UserComment $comment, bool $isReply): string
  {
    $avatarSrc = $comment->getUser()->getAvatar() ?? '/images/default/avatar_default.png';
    $rs = '<div class="studio-comment">';
    $rs .= '<img class="comment-avatar" src="'.$avatarSrc.'" alt="Card image">';
    $rs .= '<div class="comment-content">';
    $rs .= '<a href="/app/user/'.$comment->getUser()->getId().'">'.$comment->getUsername().'</a>';
    /** @var User|null $user */
    $user = $this->getUser();
    if ((StudioUser::ROLE_ADMIN === $this->studio_manager->getStudioUserRole($user, $comment->getStudio())
        || (!is_null($this->getUser()) && $this->getUser()->getUserIdentifier() === $comment->getUsername())) && $isReply) {
      $rs .= '<a class="comment-delete-button" data-bs-toggle="tooltip" onclick="(new Studio()).removeComment($(this),'.$comment->getId().', true, '.$comment->getParentId().')"';
      $rs .= ' title="'.$this->translator->trans('studio.details.remove_comment', [], 'catroweb').'">';
      $rs .= '<i class="ms-2 material-icons text-danger">delete</i>';
      $rs .= '</a>';
    }

    $rs .= '<p>'.$comment->getText().'</p>';
    $rs .= '<div class="comment-info"><span class="comment-time col-6">';
    $rs .= '<span class="material-icons comment-info-icons">watch_later</span>'.$comment->getUploadDate()->format('Y-m-d').'</span>';
    $rs .= '</div></div></div>';
    if (!$isReply) {
      $rs .= '<hr class="comment-hr">';
    }

    return $rs;
  }
}
