<?php

namespace Catrobat\CatrowebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CatrowebBundle:Default:index.html.twig');
    }
}
