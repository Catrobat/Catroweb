<?php

namespace Catrobat\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatrobatAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
