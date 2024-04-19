<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
  #[Route(path: '/login', name: 'login', methods: ['GET'])]
  public function login(): Response
  {
    return $this->render('security/login.html.twig');
  }
}
