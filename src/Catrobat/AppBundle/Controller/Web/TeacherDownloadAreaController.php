<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * Created by IntelliJ IDEA.
 * User: Wolfgang Karl
 * Date: 20.03.16
 * Time: 14:55
 */
class TeacherDownloadAreaController extends Controller
{
    const PASSWORD = "1234";

    /**
     * @Route("/teachersLogin", name="teachersLogin")
     * @Method({"GET"})
     */
    public function teachersLoginAction(Request $request)
    {
        return $this->get('templating')->renderResponse(':teachers:teachersLogin.html.twig');
        //return new Response("Seems like you'll have to login first ;-)");
    }

    /**
     * @Route("/teachers", name="teachers")
     * @Method({"GET"})
     */
    public function teachersInternalSectionAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher() === false)
        {
            return $this->redirectToRoute('teachersLogin'); // fos_user_security_login
        }

        return new Response("hello world! :)");
    }

    public function isAuthenticatedAsTeacher() {
        $session = $this->get('session');
        $isAuthenticated = $session->get("isAuthenticatedAsTeacher");
        return $isAuthenticated !== null && $isAuthenticated;
    }

    public function authenticateAsTeacher()
    {
        $session = $this->get('session');
        $session->set("isAuthenticatedAsTeacher", 1);
    }
}