<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TutorialController extends AbstractController
{
  /**
   * @Route("/help", name="catrobat_web_help", methods={"GET"})
   */
  public function helpAction(): Response
  {
    return $this->redirect('https://wiki.catrobat.org/bin/view/Documentation/');
  }
}
