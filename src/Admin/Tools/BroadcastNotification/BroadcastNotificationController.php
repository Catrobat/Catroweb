<?php

namespace App\Admin\Tools\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\User;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<BroadcastNotification>
 */
class BroadcastNotificationController extends CRUDController
{
  public function __construct(
    protected NotificationManager $notification_manager,
    protected UserManager $user_manager
  ) {
  }

  public function listAction(Request $request): Response
  {
    return $this->renderWithExtraParams('Admin/Tools/broadcast_notification.html.twig');
  }

  public function sendAction(Request $request): Response
  {
    $message = (string) $request->query->get('Message');
    $title = '';

    $this->notification_manager->addNotifications($this->getNotifications($message, $title, $this->user_manager));

    return new Response('OK');
  }

  private function getNotifications(string $message, string $title, UserManager $user_manager): \Generator
  {
    /** @var User $user */
    foreach ($user_manager->findAll() as $user) {
      yield new BroadcastNotification($user, $title, $message);
    }
  }
}
