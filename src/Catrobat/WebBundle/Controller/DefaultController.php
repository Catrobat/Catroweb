<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CatrobatWebBundle:Default:index.html.twig');
    }
}
