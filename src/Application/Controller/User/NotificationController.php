<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
  #[Route(path: '/user_notifications', name: 'notifications', methods: ['GET'])]
  public function notifications(): Response
  {
    if (!$this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
      return $this->redirectToRoute('login');
    }

    return $this->render('User/Notification/NotificationsPage.html.twig');
  }
}
