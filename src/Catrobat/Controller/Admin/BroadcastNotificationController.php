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
 * Class BroadcastNotificationController
 * @package App\Catrobat\Controller\Admin
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
   * @param Request $request
   * @param CatroNotificationService $notification_service
   * @param UserManager $user_manager
   *
   * @return Response
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function sendAction(Request $request, CatroNotificationService $notification_service, UserManager $user_manager)
  {
    $message = $request->get("Message");
    $title = $request->get("Title");

    $notification_service->addNotifications($this->getNotifications($message, $title, $user_manager));

    return new Response("OK");
  }


  /**
   * @param $message
   * @param $title
   * @param UserManager $user_manager
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