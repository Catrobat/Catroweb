<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\BroadcastNotification;
use App\Entity\UserManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BroadcastNotificationController.
 */
class BroadcastNotificationController extends CRUDController
{
  /**
   * @return Response
   */
  public function listAction()
  {
    return $this->renderWithExtraParams('Admin/broadcast_notification.html.twig');
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @return Response
   */
  public function sendAction(Request $request, CatroNotificationService $notification_service, UserManager $user_manager)
  {
    $message = $request->get('Message');
    $title = $request->get('Title');

    $notification_service->addNotifications($this->getNotifications($message, $title, $user_manager));

    return new Response('OK');
  }

  /**
   * @param $message
   * @param $title
   *
   * @return \Generator
   */
  private function getNotifications($message, $title, UserManager $user_manager)
  {
    foreach ($user_manager->findAll() as $user)
    {
      yield new BroadcastNotification($user, $title, $message);
    }
  }
}
