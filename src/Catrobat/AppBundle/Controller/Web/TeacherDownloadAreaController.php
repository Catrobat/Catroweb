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
        if($this->isAuthenticatedAsTeacher()) {
            return $this->redirectToRoute('teachers');
        }
        return $this->get('templating')->renderResponse(':teachers:teachersLogin.html.twig');
    }

    /**
     * @Route("/teachersLogout", name="teachersLogout")
     * @Method({"GET"})
     */
    public function teachersLogoutAction(Request $request)
    {
        $session = $this->get('session');
        $session->set("isAuthenticatedAsTeacher", 0);
        return $this->redirectToRoute('teachersLogin');
    }

    /**
     * @Route("/teachersAuth", name="teachersAuth")
     * @Method({"POST"})
     */
    public function teachersLoginPostAction(Request $request)
    {
        $password = $request->get("password");
        if(self::PASSWORD === $password) {
            self::authenticateAsTeacher();
            return $this->redirectToRoute('teachers');
        }
        return $this->redirectToRoute('teachersLogin');
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

        return $this->get('templating')->renderResponse(':teachers:teachers.html.twig');
    }

    /**
     * @Route("/teachersDownload.catrobat", name="teachersDownload.catrobat")
     * @Method({"GET"})
     */
    public function teachersDownloadAction(Request $request)
    {
        $file = $this->get('kernel')->getRootDir()."/../web/resources/teachers/templates.catrobat";

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            return new Response("No template has been uploaded so far.");
        }
    }

    /**
     * @Route("/teachersTemplateUpload", name="teachersTemplateUpload")
     * @Method({"GET"})
     */
    public function teachersUploadAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher() === false)
        {
            return $this->redirectToRoute('teachersLogin'); // fos_user_security_login
        }

        return $this->get('templating')->renderResponse(':teachers:teachersUpload.html.twig');
    }

    /**
     * @Route("/teachersTemplatePostUpload", name="teachersTemplatePostUpload")
     * @Method({"POST"})
     */
    public function teachersUploadPostAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher() === false)
        {
            return $this->redirectToRoute('teachersLogin'); // fos_user_security_login
        }

        $file = $this->get('kernel')->getRootDir()."/../web/resources/teachers/templates.catrobat";

        if (move_uploaded_file($_FILES['templates']['tmp_name'], $file)) {
            return new Response("Templates have been uploaded successfully!");
        } else {
            return new Response("Failed to upload templates!");
        }

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