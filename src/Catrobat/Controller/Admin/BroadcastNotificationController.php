<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\BroadcastNotification;
use App\Entity\UserManager;
use Generator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BroadcastNotificationController extends CRUDController
{
  public function listAction(): Response
  {
    return $this->renderWithExtraParams('Admin/broadcast_notification.html.twig');
  }

  public function sendAction(Request $request, CatroNotificationService $notification_service, UserManager $user_manager): Response
  {
    $message = $request->get('Message');
    $title = '';

    $notification_service->addNotifications($this->getNotifications($message, $title, $user_manager));

    return new Response('OK');
  }

  /**
   * @psalm-return \Generator<int, BroadcastNotification, mixed, void>
   */
  private function getNotifications(string $message, string $title, UserManager $user_manager): Generator
  {
    foreach ($user_manager->findAll() as $user)
    {
      yield new BroadcastNotification($user, $title, $message);
    }
  }
}
