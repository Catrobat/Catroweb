<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function headerAction()
    {
        return $this->render('CatrobatWebBundle:Default:header.html.twig');
    }

    public function footerAction()
    {
        return $this->render('CatrobatWebBundle:Default:footer.html.twig');
    }

    public function showTermsOfUseAction()
    {
        return $this->render('CatrobatWebBundle::termsOfUse.html.twig');
    }

    public function showLicenseToPlayAction()
    {
      return $this->render('CatrobatWebBundle::licenseToPlay.html.twig');
    }
}
