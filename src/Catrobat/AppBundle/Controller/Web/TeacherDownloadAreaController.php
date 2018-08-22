<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\TeacherTemplate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
     * @Route("/teachersLogin", name="teachersLogin", methods={"GET"})
     */
    public function teachersLoginAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher()) {
            return $this->redirectToRoute('teachers');
        }
        return $this->get('templating')->renderResponse(':teachers:teachersLogin.html.twig');
    }

    /**
     * @Route("/teachersLogout", name="teachersLogout", methods={"GET"})
     */
    public function teachersLogoutAction(Request $request)
    {
        $session = $this->get('session');
        $session->set("isAuthenticatedAsTeacher", 0);
        return $this->redirectToRoute('teachersLogin');
    }

    /**
     * @Route("/teachersAuth", name="teachersAuth", methods={"POST"})
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
     * @Route("/teachers", name="teachers", methods={"GET"})
     */
    public function teachersInternalSectionAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher() === false)
        {
            return $this->redirectToRoute('teachersLogin'); // fos_user_security_login
        }

        $templates = $this->getDoctrine()
            ->getRepository('AppBundle:TeacherTemplate')
            ->findAll();

        return $this->get('templating')->renderResponse(':teachers:teachers.html.twig', array('templates' => $templates));
    }

    /**
     * @Route("/teachersDownload/{program}", name="teachersDownload", methods={"GET"})
     */
    public function teachersDownloadAction(Request $request, $program)
    {
        $id = rtrim($program, ".catrobat");

        $template = $this->getDoctrine()
            ->getRepository('AppBundle:TeacherTemplate')
            ->find($id);

        if(!$template) {
            die("No such template");
        }

        $file = $template->getFileSystemLocation();
        //$file = $this->get('kernel')->getRootDir()."/../web/resources/teachers/templates.zip";

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.$program.'"');
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
     * @Route("/teachersTemplateUpload", name="teachersTemplateUpload", methods={"GET"})
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
     * @Route("/teachersTemplatePostUpload", name="teachersTemplatePostUpload", methods={"POST"})
     */
    public function teachersUploadPostAction(Request $request)
    {
        if($this->isAuthenticatedAsTeacher() === false)
        {
            return $this->redirectToRoute('teachersLogin'); // fos_user_security_login
        }

        $file = $this->get('kernel')->getRootDir()."/../web/resources/teachers/" . basename($_FILES['templates']['tmp_name']);

        if (move_uploaded_file($_FILES['templates']['tmp_name'], $file)) {
            $name = basename($file);
            $zip = zip_open($file);

            if ($zip)
            {
                if($zip_entry = zip_read($zip))
                {
                    $name = rtrim(zip_entry_name($zip_entry), "/");
                }
            }

            zip_close($zip);

            $template = new TeacherTemplate();
            $template->setFileSystemLocation($file);
            $template->setFriendlyName($name);
            $template->setPriority(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush();

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