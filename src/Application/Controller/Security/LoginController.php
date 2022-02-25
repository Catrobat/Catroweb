<?php

namespace App\Application\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
  /**
   * @Route("/login", name="login", methods={"GET"})
   */
  public function logoutAction(): Response
  {
    return $this->render('security/login.html.twig');
  }
}
