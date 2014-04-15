<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
//    public function indexAction()
//    {
//        return $this->render('CatrobatWebBundle:Default:layout.html.twig');
//    }

    public function headerAction()
    {
        return $this->render('CatrobatWebBundle:Default:header.html.twig');
    }

    public function footerAction()
    {
        return $this->render('CatrobatWebBundle:Default:footer.html.twig');
    }
}
