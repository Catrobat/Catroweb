<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\CommentNotification;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentsController
 * @package Catrobat\AppBundle\Controller\Web
 */
class CommentsController extends Controller
{
  /**
   * @Route("/reportComment", name="report", methods={"GET"})
   *
   * @throws \Exception
   * @return Response
   */
  public function reportCommentAction()
  {
    $user = $this->getUser();
    if (!$user)
    {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $_GET['CommentId']
      );
    }

    $comment->setIsReported(true);
    $em->flush();

    return new Response(StatusCode::OK);
  }

  /**
   * @Route("/deleteComment", name="delete", methods={"GET"})
   *
   * @throws \Exception
   * @return Response
   */
  public function deleteCommentAction()
  {
    /**
     * @var $comment UserComment
     * @var $user    User
     */
    $user = $this->getUser();
    if (!$user)
    {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if ($user->getId() !== $comment->getUserId() && !$this->isGranted("ROLE_ADMIN"))
    {
      return new Response(StatusCode::NO_ADMIN_RIGHTS);
    }

    // first remove notification if there is one!
    $notification = $em->getRepository(CommentNotification::class)->find($_GET['CommentId']);
    if ($notification)
    {
      $em->remove($notification);
    }

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $_GET['CommentId']
      );
    }
    $em->remove($comment);
    $em->flush();

    return new Response(StatusCode::OK);
  }

  /**
   * @Route("/comment", name="comment", methods={"POST"})
   *
   * @throws \Exception
   * @return Response
   */
  public function postCommentAction()
  {
    /**
     * @var $user             User
     * @var $program          Program
     * @var $reported_program ProgramInappropriateReport
     */

    $user = $this->getUser();
    if (!$user)
    {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $notification_service = $this->get("catro_notification_service");

    $user = $this->get("security.token_storage")->getToken()->getUser();
    $id = $user->getId();

    $program_manager = $this->get("programmanager");
    $program = $program_manager->find($_POST['ProgramId']);

    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUserId($id);
    $temp_comment->setText($_POST['Message']);
    $temp_comment->setProgram($program);
    $temp_comment->setProgramId($program->getId());
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($temp_comment);
    $em->flush();

    $em->refresh($temp_comment);

    if ($user !== $program->getUser())
    {
      $notification = new CommentNotification($program->getUser(),
        "Comment notification",
        "You received a new comment on program %programname% from user %author%.", $temp_comment);
      $notification_service->addNotification($notification);
    }

    return new Response(StatusCode::OK);
  }
}
