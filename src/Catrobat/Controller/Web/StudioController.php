<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\ProgramManager;
use App\Entity\StudioActivity;
use App\Entity\StudioUser;
use App\Entity\UserComment;
use App\Entity\UserManager;
use App\Manager\StudioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudioController extends AbstractController
{
  protected StudioManager $studio_manager;
  protected UserManager $user_manager;
  protected ProgramManager $program_manager;
  protected ScreenshotRepository $screenshot_repository;
  protected TranslatorInterface $translator;
  protected ParameterBagInterface $parameter_bag;

  public function __construct(StudioManager $studio_manager, UserManager $user_manager,
                              ProgramManager $program_manager, ScreenshotRepository $screenshot_repository,
                              TranslatorInterface $translator, ParameterBagInterface $parameter_bag)
  {
    $this->studio_manager = $studio_manager;
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->screenshot_repository = $screenshot_repository;
    $this->translator = $translator;
    $this->parameter_bag = $parameter_bag;
  }

  /**
   * @Route("/studio/{id}", name="studio_details", methods={"GET"})
   */
  public function studioDetails(Request $request): Response
  {
    $studio = $this->studio_manager->findStudioById(trim($request->attributes->get('id')));
    if (is_null($studio)) {
      throw $this->createNotFoundException('Unable to find this studio');
    }
    $user_role = $this->studio_manager->getStudioUserRole($this->getUser(), $studio);
    $members_count = $this->studio_manager->findStudioUsersCount($studio);
    $activities_count = $this->studio_manager->findStudioActivitiesCount($studio);
    $projects = $this->studio_manager->findAllStudioProjects($studio);
    $projects_count = $this->studio_manager->findStudioProjectsCount($studio);
    $comments_count = $this->studio_manager->findStudioCommentsCount($studio);
    $comments = $this->studio_manager->findAllStudioComments($studio);

    return $this->render('Studio/studio_details.html.twig',
      ['studio' => $studio, 'user_name' => !is_null($this->getUser()) ? $this->getUser()->getUsername() : '',
        'user_role' => $user_role, 'members_count' => $members_count,
        'activities_count' => $activities_count,
        'projects_count' => $projects_count, 'projects' => $this->getStudioProjectsListWithImg($projects),
        'comments_count' => $comments_count, 'comments' => $this->getStudioCommentsListWithAvatar($comments),
      ]);
  }

  /**
   * @Route("/removeStudioProject/", name="remove_studio_project", methods={"POST"})
   */
  public function removeProjectFromStudio(Request $request): JsonResponse
  {
    $project = $this->program_manager->find(trim($request->request->get('projectID')));
    $studio = $this->studio_manager->findStudioById(trim($request->request->get('studioID')));
    if (is_null($project) || is_null($studio)) {
      return new JsonResponse(Response::HTTP_NOT_FOUND);
    }
    $this->studio_manager->deleteProjectFromStudio($this->getUser(), $studio, $project);
    $projects_count = ' ('.$this->studio_manager->findStudioProjectsCount($studio).')';
    $activities_count = $this->studio_manager->findStudioActivitiesCount($studio);
    if (is_null($this->studio_manager->findStudioProject($studio, $project))) {
      return new JsonResponse(['projects_count' => $projects_count, 'activities_count' => $activities_count], Response::HTTP_OK);
    }

    return new JsonResponse([], Response::HTTP_NOT_FOUND);
  }

  /**
   * @Route("/removeStudioComment/", name="remove_studio_comment", methods={"POST"})
   */
  public function removeCommentFromStudio(Request $request): JsonResponse
  {
    $comment_id = intval($request->request->get('commentID'));
    $isReply = trim($request->request->get('isReply'));
    $parent_id = intval($request->request->get('parentID'));
    $studio = $this->studio_manager->findStudioById(trim($request->request->get('studioID')));
    if (!$comment_id || is_null($studio)) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }
    $replies_count = null;
    $this->studio_manager->deleteCommentFromStudio($this->getUser(), $comment_id);
    if ('true' === $isReply && $parent_id > 0) {
      $replies_count = $this->studio_manager->findCommentRepliesCount($parent_id).' '.$this->translator->trans('studio.details.replies', [], 'catroweb');
    }
    $comments_count = ' ('.$this->studio_manager->findStudioCommentsCount($studio).')';
    $activities_count = $this->studio_manager->findStudioActivitiesCount($studio);
    if (is_null($this->studio_manager->findStudioCommentById($comment_id))) {
      return new JsonResponse(['comments_count' => $comments_count, 'activities_count' => $activities_count,
        'replies_count' => $replies_count, ], Response::HTTP_OK);
    }

    return new JsonResponse([], Response::HTTP_NOT_FOUND);
  }

  /**
   * @Route("/postCommentToStudio/", name="post_studio_comment", methods={"POST"})
   */
  public function postComment(Request $request): JsonResponse
  {
    $isReply = 'true' == $request->request->get('isReply') && intval($request->request->get('parentID')) > 0;
    $studio = $this->studio_manager->findStudioById(trim($request->request->get('studioID')));
    $comment_text = trim($request->request->get('comment'));
    if ('' === $comment_text) {
      return new JsonResponse('', Response::HTTP_NOT_FOUND);
    }
    $replies_count = null;
    $comments_count = null;
    if ($isReply) {
      $comment = $this->studio_manager->addCommentToStudio($this->getUser(), $studio, $comment_text, intval($request->request->get('parentID')));
      $replies_count = $this->studio_manager->findCommentRepliesCount(intval($request->request->get('parentID'))).' '.$this->translator->trans('studio.details.replies', [], 'catroweb');
    } else {
      $comment = $this->studio_manager->addCommentToStudio($this->getUser(), $studio, $comment_text);
      $comments_count = ' ('.$this->studio_manager->findStudioCommentsCount($studio).')';
    }
    $activities_count = $this->studio_manager->findStudioActivitiesCount($studio);
    $avatarSrc = $comment->getUser()->getAvatar() ?? '/images/default/avatar_default.png';
    $result = '<div class="studio-comment">';
    $result .= '<img class="comment-avatar" src="'.$avatarSrc.'" alt="Card image">';
    $result .= '<div class="comment-content">';
    $result .= '<a href="/app/user/'.$comment->getId().'">'.$comment->getUsername().'</a>';
    $result .= '<a class="comment-delete-button" data-toggle="tooltip" onclick="';
    $result .= '(new Studio()).removeComment($(this), '.$comment->getId().',';
    $result .= $isReply ? 'true,'.$comment->getParentId().')">' : 'false, 0)">';
    $result .= '<i class="ml-2 material-icons text-danger">delete</i></a>';
    $result .= '<p>'.$comment->getText().'</p>';
    $result .= '<div class="comment-info">';
    $result .= '<span class="comment-time col-6">';
    $result .= '<span class="material-icons comment-info-icons">watch_later</span>'.$comment->getUploadDate()->format('Y-m-d').'</span>';
    if (!$isReply) {
      $result .= '<a class="comment-replies col-6" onclick="(new Studio()).loadReplies('.$comment->getId().')" data-toggle="modal" data-target="#comment-reply-modal">';
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
   * @Route("/loadCommentReplies/", name="load_comment_replies", methods={"GET"})
   */
  public function loadCommentReplies(Request $request): Response
  {
    $rs = '';
    $comment_id = intval($request->query->get('commentID'));
    $comment = $this->studio_manager->findStudioCommentById($comment_id);
    if (is_null($comment)) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }
    $rs .= $this->getCommentsAndRepliesForAjax($comment, false);
    $replies = $this->studio_manager->findCommentReplies($comment_id);
    foreach ($replies as $reply) {
      $rs .= $this->getCommentsAndRepliesForAjax($reply, true);
    }
    if (!is_null($this->getUser()) && $this->studio_manager->isUserInStudio($this->getUser(), $comment->getStudio())) {
      $rs .= '<div id="add-reply" class="add-comment-section">';
      $rs .= '<input type="text" placeholder="'.$this->translator->trans('studio.details.type_something', [], 'catroweb').'">';
      $rs .= '<a href="javascript:void(0)" onclick="(new Studio()).postComment(true)">';
      $rs .= $this->translator->trans('studio.details.send_comment', [], 'catroweb');
      $rs .= '</a></div>';
    }

    return new JsonResponse($rs, Response::HTTP_OK);
  }

  /**
   * @Route("/uploadStudioCover/", name="upload_studio_cover", methods={"POST"})
   */
  public function uploadStudioCover(Request $request): Response
  {
    $studio = $this->studio_manager->findStudioById(trim($request->request->get('std-id')));
    $headerImg = $request->files->get('header-img');
    if (is_null($headerImg) || is_null($studio) || is_null($this->getUser())) {
      return new JsonResponse([], Response::HTTP_NOT_FOUND);
    }
    $newPath = 'images/Studios/';
    $coverPath = $this->parameter_bag->get('catrobat.pubdir').$newPath;
    $coverName = (new \DateTime())->getTimestamp().$headerImg->getClientOriginalName();
    if (!file_exists($coverPath)) {
      $fs = new Filesystem();
      $fs->mkdir($coverPath);
    }
    $headerImg->move($coverPath, $coverName);
    $pathToSave = '/'.$newPath.$coverName;
    $studio->setCoverPath($pathToSave);
    $this->studio_manager->editStudio($this->getUser(), $studio);

    return new JsonResponse(['new_cover' => $pathToSave], Response::HTTP_OK);
  }

  protected function getStudioProjectsListWithImg(array $studioProjects): array
  {
    $rs = [];
    foreach ($studioProjects as $studioProject) {
      $project = [];
      $project['id'] = $studioProject->getProgram()->getId();
      $project['name'] = $studioProject->getProgram()->getName();
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
      $commentsObj['repliesCount'] = $this->studio_manager->findCommentRepliesCount($studioComment->getId());
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
    if ((StudioUser::ROLE_ADMIN === $this->studio_manager->getStudioUserRole($this->getUser(), $comment->getStudio())
      || (!is_null($this->getUser()) && $this->getUser()->getUsername() === $comment->getUsername())) && $isReply) {
      $rs .= '<a class="comment-delete-button" data-toggle="tooltip" onclick="(new Studio()).removeComment($(this),'.$comment->getId().', true, '.$comment->getParentId().')"';
      $rs .= ' title="'.$this->translator->trans('studio.details.remove_comment', [], 'catroweb').'">';
      $rs .= '<i class="ml-2 material-icons text-danger">delete</i>';
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

  /**
   * @Route("/loadActivitesList/", name="load_activites_list", methods={"GET"})
   */
  public function loadActivitiesList(Request $request): Response
  {
    $studio = $this->studio_manager->findStudioById(trim($request->query->get('studioID')));
    if (is_null($studio)) {
      throw $this->createNotFoundException('Unable to find this studio');
    }
    $activities = $this->studio_manager->findAllStudioActivitiesCombined($studio);
    $rs = '';
    if (count($activities) > 0) {
      $rs = $this->getRenderedActivitiesForAjax($activities);

      return new JsonResponse($rs, Response::HTTP_OK);
    }

    return new JsonResponse($rs, Response::HTTP_NOT_FOUND);
  }

  protected function getRenderedActivitiesForAjax(array $activities): string
  {
    $rs = '';
    foreach ($activities as $activity) {
      if (is_null($activity)) {
        continue;
      }
      $rs .= '<li>';
      $rs .= '<a href="/app/user/'.$activity->getActivity()->getUser()->getId().'">';
      $rs .= '<span class="activity-user">';
      $rs .= $activity->getActivity()->getUser()->getUserName();
      $rs .= '</span>';
      $rs .= '</a>&emsp13;';
      $rs .= '<span class="activity-type">';
      switch ($activity->getActivity()->getType()) {
        case StudioActivity::TYPE_COMMENT:
          if (intval($activity->getParentId()) > 0) {
            $rs = '';
            break;
          }
          $rs .= $this->translator->trans('studio.details.activity_add_comment', [], 'catroweb');
          $rs .= '</span>&emsp13;';
          $rs .= '<span class="activity-object">';
          $rs .= $activity->getText();
          $rs .= '</span>.&emsp13;';
          $rs .= '<span class="activity-time">';
          $rs .= $activity->getActivity()->getCreatedOn()->format('Y-m-d');
          $rs .= '</span>';
          $rs .= '</li>';
          break;
        case StudioActivity::TYPE_PROJECT:
          $rs .= $this->translator->trans('studio.details.activity_add_project',
            ['%project%' => '<a href="/app/project/'.$activity->getProgram()->getId().'" >
            <span class="activity-object">'.$activity->getProgram()->getName().'</span></a>'], 'catroweb');
          $rs .= '</span>.&emsp13;';
          $rs .= '<span class="activity-time">';
          $rs .= $activity->getActivity()->getCreatedOn()->format('Y-m-d');
          $rs .= '</span>';
          $rs .= '</li>';
          break;
        case StudioActivity::TYPE_USER:
          if ($activity->getActivity()->getUser()->getId() === $activity->getUser()->getId()) {
            $rs .= $this->translator->trans('studio.details.created_studio', [], 'catroweb');
          } else {
            $rs .= $this->translator->trans('studio.details.activity_add_user',
              ['%user%' => '<a href="/app/user/'.$activity->getUser()->getId().'" ><span class="activity-object">'.$activity->getUser()->getUserName().'</span></a>'], 'catroweb');
          }
          $rs .= '</span>.&emsp13;';
          $rs .= '<span class="activity-time">';
          $rs .= $activity->getActivity()->getCreatedOn()->format('Y-m-d');
          $rs .= '</span>';
          $rs .= '</li>';
          break;
        default:
          $rs .= '';
      }
    }

    return $rs;
  }
}
