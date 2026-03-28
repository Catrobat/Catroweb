<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportsController extends AbstractController
{
  #[Route(path: '/user_reports', name: 'user_reports', methods: ['GET'])]
  public function reports(): Response
  {
    if (!$this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
      return $this->redirectToRoute('login');
    }

    return $this->render('User/Reports/ReportsPage.html.twig');
  }
}
