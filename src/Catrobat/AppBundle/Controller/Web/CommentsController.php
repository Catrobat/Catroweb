<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\CommentNotification;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentsController extends Controller
{
  /**
   * @Route("/report", name="report", methods={"GET"})
   */
  public function reportCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return new Response("log_in");
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $_GET['CommentId']
      );
    }

    $comment->setIsReported(true);
    $em->flush();

    return new Response("Comment successfully reported!");
  }

  /**
   * @Route("/delete", name="delete", methods={"GET"})
   */
  public function deleteCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return new Response("log_in");
    }

    if (!$this->isGranted("ROLE_ADMIN"))
    {
      return new Response("no_admin");
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $_GET['CommentId']
      );
    }
    $em->remove($comment);
    $em->flush();

    return new Response("ok");
  }

  /**
   * @Route("/comment", name="comment", methods={"POST"})
   */
  public function postCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return new Response("log_in");
    }

    $notification_service = $this->get("catro_notification_service");

    $token = $this->get("security.token_storage")->getToken();
    $user = $token->getUser();
    $id = $user->getId();

    $pm = $this->get("programmanager");
    $program = $pm->find($_POST['ProgramId']);

    /**
     * @var $user             User
     * @var $program          Program
     * @var $reported_program ProgramInappropriateReport
     */

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

    $notification = new CommentNotification($program->getUser(),
      "Comment notification",
      "You received a new comment on program %programname% from user %author%.", $temp_comment);
    $notification_service->addNotification($notification);

    return new Response("ok");
  }
}
