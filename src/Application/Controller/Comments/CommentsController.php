<?php

declare(strict_types=1);

namespace App\Application\Controller\Comments;

use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentsController extends AbstractController
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  /**
   * @throws \Exception
   */
  #[Route(path: '/reportComment/{id}', name: 'report', defaults: ['id' => 0], methods: ['DELETE'])]
  public function report(int $id): Response
  {
    $user = $this->getUser();
    if (!$user instanceof UserInterface) {
      return new Response('', Response::HTTP_UNAUTHORIZED);
    }

    $comment = $this->entity_manager->getRepository(UserComment::class)->find($id);
    if (null === $comment) {
      return new Response('No comment found for this id '.$id, Response::HTTP_NOT_FOUND);
    }

    if ($comment->getIsDeleted()) {
      return new Response('A deleted comment cannot be reported', Response::HTTP_BAD_REQUEST);
    }

    $comment->setIsReported(true);
    $this->entity_manager->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * @throws \Exception
   */
  #[Route(path: '/deleteComment/{id}', name: 'delete', defaults: ['id' => 0], methods: ['DELETE'])]
  public function deleteComment(int $id): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return new Response('', Response::HTTP_UNAUTHORIZED);
    }

    if ($id <= 0) {
      return new Response('Invalid comment id', Response::HTTP_BAD_REQUEST);
    }

    $comment = $this->entity_manager->getRepository(UserComment::class)->find($id);
    if (null === $comment) {
      return new Response('No comment found for this id '.$id, Response::HTTP_NOT_FOUND);
    }

    if ($comment->getIsDeleted()) {
      return new Response('An already deleted comment cannot be deleted', Response::HTTP_BAD_REQUEST);
    }

    $comment_user_id = 0;
    if ($comment->getUser()) {
      $comment_user_id = $comment->getUser()->getId();
    }

    if ($user->getId() !== $comment_user_id && !$this->isGranted('ROLE_ADMIN')) {
      return new Response('', Response::HTTP_FORBIDDEN);
    }

    $comment->setIsDeleted(true);

    $this->entity_manager->persist($comment);
    $this->entity_manager->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }

  /**
   * @throws ORMException
   */
  #[Route(path: '/comment', name: 'comment', methods: ['POST'])]
  public function postComment(Request $request, NotificationManager $notification_service, ProjectManager $project_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new Response('', Response::HTTP_UNAUTHORIZED);
    }

    $data = json_decode($request->getContent(), true);
    $message = (string) $data['Message'];
    $program_id = (string) $data['ProgramId'];
    $parent_comment_id = (int) ($data['ParentCommentId'] ?? null);

    $project = $project_manager->find($program_id);
    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUserIdentifier());
    $temp_comment->setUser($user);
    $temp_comment->setText($message);
    $temp_comment->setProgram($project);

    $date_time_zone = new \DateTimeZone('UTC');
    $temp_comment->setUploadDate(date_create('now', $date_time_zone));
    $temp_comment->setIsReported(false);
    $temp_comment->setIsDeleted(false);
    if ($parent_comment_id > 0) {
      $temp_comment->setParentId($parent_comment_id);
    }

    $this->entity_manager->persist($temp_comment);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($temp_comment);
    if ($user !== $project->getUser()) {
      $notification = new CommentNotification($project->getUser(), $temp_comment);
      $notification_service->addNotification($notification);

      // Telling the new comment the CommentNotification it triggered. This is necessary to ensure the
      // correct remove-cascade
      $temp_comment->setNotification($notification);
      $this->entity_manager->persist($temp_comment);
      $this->entity_manager->flush();
    }

    $isAdmin = $this->isGranted('ROLE_ADMIN');

    $comment_data = [
      'id' => $temp_comment->getId(),
      'username' => $temp_comment->getUsername(),
      'text' => $temp_comment->getText(),
      'is_deleted' => $temp_comment->getIsDeleted(),
      'upload_date' => $temp_comment->getUploadDate(),
      'user_id' => $user->getId(),
      'user_avatar' => $user->getAvatar(),
    ];

    if ($parent_comment_id <= 0) {
      $comment_data['number_of_replies'] = 0;
    }

    $rendered_comment = $this->renderView('Project/Comment/Comment.html.twig', [
      'comment' => $comment_data,
      'isAdmin' => $isAdmin,
      'are_replies' => $parent_comment_id > 0,
    ]);

    return new JsonResponse([
      'rendered' => $rendered_comment,
    ]);
  }

  #[Route(path: '/translate/comment/{id}', name: 'translate_comment', methods: ['GET'])]
  public function translateComment(Request $request, int $id, TranslationDelegate $translation_delegate): Response
  {
    if (!$request->query->has('target_language')) {
      return new Response('Target language is required', Response::HTTP_BAD_REQUEST);
    }

    $comment = $this->entity_manager->getRepository(UserComment::class)->find($id);

    if (null === $comment) {
      return new Response('No comment found for this id', Response::HTTP_NOT_FOUND);
    }

    if ($comment->getIsDeleted()) {
      return new Response('A deleted comment cannot be translated', Response::HTTP_BAD_REQUEST);
    }

    $source_language = $request->query->get('source_language');
    $source_language = is_null($source_language) ? $source_language : (string) $source_language;

    $target_language = (string) $request->query->get('target_language');
    if ($source_language === $target_language) {
      return new Response('Source and target languages are the same', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $response = new JsonResponse();
    $response->setEtag(md5((string) $comment->getText()).$target_language);
    $response->setPublic();
    if ($response->isNotModified($request)) {
      return $response;
    }

    try {
      $translation_result = $translation_delegate->translate($comment->getText(), $source_language, $target_language);
    } catch (\InvalidArgumentException $invalidArgumentException) {
      return new Response($invalidArgumentException->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    if (!$translation_result instanceof TranslationResult) {
      return new Response('Translation unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
    }

    return $response->setData([
      'id' => $comment->getId(),
      'source_language' => $source_language ?? $translation_result->detected_source_language,
      'target_language' => $target_language,
      'translation' => $translation_result->translation,
      'provider' => $translation_result->provider,
      '_cache' => null,
    ]);
  }
}
