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
     * @Route("/teachers", name="teachers")
     * @Method({"GET"})
     */
    public function teachersAction(Request $request)
    {
        return new Response("hello world! :)");
    }
}