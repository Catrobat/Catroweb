<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\StatusCode;
use App\Entity\CommentNotification;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserComment;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class CommentsController
 * @package App\Catrobat\Controller\Web
 */
class CommentsController extends AbstractController
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
   * @param CatroNotificationService $notification_service
   * @param ProgramManager $program_manager
   *
   * @return Response
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function postCommentAction(CatroNotificationService $notification_service, ProgramManager $program_manager)
  {
    /**
     * @var $user             User
     * @var $program          Program
     * @var $reported_program ProgramInappropriateReport
     * @var $program_manager  ProgramManager
     */

    $user = $this->getUser();
    if (!$user)
    {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $user = $this->get("security.token_storage")->getToken()->getUser();
    $id = $user->getId();

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
      $notification = new CommentNotification($program->getUser(), $temp_comment);
      $notification_service->addNotification($notification);
    }

    return new Response(StatusCode::OK);
  }
}
