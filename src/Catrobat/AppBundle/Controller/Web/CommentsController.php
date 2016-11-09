<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentsController extends Controller
{
    /**
     * @Route("/report", name="report")
     * @Method({"GET"})
     */
    public function reportCommentAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response("log_in");
        }

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for this id '.$_GET['CommentId']
            );
        }

        $comment->setIsReported(true);
        $em->flush();
        return new Response("Comment successfully reported!");
    }

    /**
     * @Route("/delete", name="delete")
     * @Method({"GET"})
     */
    public function deleteCommentAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response("log_in");
        }

        if (!$this->isGranted("ROLE_ADMIN"))
        {
            return new Response("no_admin");
        }
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for this id '.$_GET['CommentId']
            );
        }
        $em->remove($comment);
        $em->flush();
        return new Response("ok");
    }

    /**
     * @Route("/comment", name="comment")
     * @Method({"POST"})
     */
    public function postCommentAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response("log_in");
        }

        $token = $this->get("security.token_storage")->getToken();
        $user = $token->getUser();
        $id = $user->getId();

        /**
         * @var $user User
         * @var $program Program
         * @var $reported_program ProgramInappropriateReport
         */

        $temp_comment = new UserComment();
        $temp_comment->setUsername($user->getUsername());
        $temp_comment->setUserId($id);
        $temp_comment->setText($_POST['Message']);
        $temp_comment->setProgramId($_POST['ProgramId']);
        $temp_comment->setUploadDate(date_create());
        $temp_comment->setIsReported(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($temp_comment);
        $em->flush();
        return new Response("ok");
    }
}
