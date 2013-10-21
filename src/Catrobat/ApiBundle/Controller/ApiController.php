<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    public function checkTokenAction()
    {
        return $this->render('CatrobatApiBundle:Api:checkToken.json.twig');
    }

    public function uploadAction()
    {
    	return $this->render('CatrobatApiBundle:Api:index.json.twig');
    }
    
}
