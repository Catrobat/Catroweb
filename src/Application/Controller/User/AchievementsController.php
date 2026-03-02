<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AchievementsController extends AbstractController
{
  #[Route(path: '/achievements', name: 'achievements_overview', methods: ['GET'])]
  public function achievementsOverview(): Response
  {
    if (null === $this->getUser()) {
      return $this->redirectToRoute('login');
    }

    return $this->render('User/Achievements/AchievementsPage.html.twig');
  }
}
