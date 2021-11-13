<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\StatusCode;
use App\Entity\CommentNotification;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserComment;
use App\Translation\TranslationDelegate;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentsController extends AbstractController
{
  /**
   * @Route("/reportComment", name="report", methods={"GET"})
   *
   * @throws Exception
   */
  public function reportCommentAction(): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if (null === $comment) {
      throw $this->createNotFoundException('No comment found for this id '.$_GET['CommentId']);
    }

    $comment->setIsReported(true);
    $em->flush();

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/deleteComment", name="delete", methods={"GET"})
   *
   * @throws Exception
   */
  public function deleteCommentAction(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if (null === $comment) {
      throw $this->createNotFoundException('No comment found for this id '.$_GET['CommentId']);
    }

    $comment_user_id = 0;
    if ($comment->getUser()) {
      $comment_user_id = $comment->getUser()->getId();
    }

    if ($user->getId() !== $comment_user_id && !$this->isGranted('ROLE_ADMIN')) {
      return new Response(StatusCode::NO_ADMIN_RIGHTS);
    }

    $em->remove($comment);
    $em->flush();

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/comment", name="comment", methods={"POST"})
   */
  public function postCommentAction(CatroNotificationService $notification_service, ProgramManager $program_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $user = $this->get('security.token_storage')->getToken()->getUser();

    $program = $program_manager->find($_POST['ProgramId']);

    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUser($user);
    $temp_comment->setText($_POST['Message']);
    $temp_comment->setProgram($program);
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($temp_comment);
    $em->flush();

    $em->refresh($temp_comment);

    if ($user !== $program->getUser()) {
      $notification = new CommentNotification($program->getUser(), $temp_comment);
      $notification_service->addNotification($notification);

      // Telling the new comment the CommentNotification it triggered. This is necessary to ensure the
      // correct remove-cascade
      $temp_comment->setNotification($notification);
      $em->persist($temp_comment);
      $em->flush();
    }

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/translate/comment/{id}", name="translate_comment", methods={"GET"})
   */
  public function translateCommentAction(Request $request, int $id, TranslationDelegate $translation_delegate): Response
  {
    if (!$request->query->has('target_language')) {
      return new Response('Target language is required', Response::HTTP_BAD_REQUEST);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($id);

    if (null === $comment) {
      return new Response('No comment found for this id', Response::HTTP_NOT_FOUND);
    }

    $source_language = $request->query->get('source_language');
    $target_language = $request->query->get('target_language');

    if ($source_language === $target_language) {
      return new Response('Source and target languages are the same', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    try {
      $translation_result = $translation_delegate->translate($comment->getText(), $source_language, $target_language);
    } catch (InvalidArgumentException $exception) {
      return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    if (null === $translation_result) {
      return new Response('Translation unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
    }

    return new JsonResponse([
      'id' => $comment->getId(),
      'source_language' => $source_language ?? $translation_result->detected_source_language,
      'target_language' => $target_language,
      'translation' => $translation_result->translation,
      'provider' => $translation_result->provider,
    ]);
  }
}
