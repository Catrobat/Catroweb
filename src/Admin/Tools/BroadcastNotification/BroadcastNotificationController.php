<?php

namespace App\Admin\Tools\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Generator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BroadcastNotificationController extends CRUDController
{
  protected NotificationManager $notification_manager;
  protected UserManager $user_manager;

  public function __construct(NotificationManager $notification_service, UserManager $user_manager)
  {
    $this->notification_manager = $notification_service;
    $this->user_manager = $user_manager;
  }

  public function listAction(): Response
  {
    return $this->renderWithExtraParams('Admin/Tools/broadcast_notification.html.twig');
  }

  public function sendAction(Request $request): Response
  {
    $message = $request->get('Message');
    $title = '';

    $this->notification_manager->addNotifications($this->getNotifications($message, $title, $this->user_manager));

    return new Response('OK');
  }

  private function getNotifications(string $message, string $title, UserManager $user_manager): Generator
  {
    foreach ($user_manager->findAll() as $user) {
      yield new BroadcastNotification($user, $title, $message);
    }
  }
}
