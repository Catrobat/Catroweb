<?php

namespace App\Catrobat\Controller\Admin;


use App\Entity\BroadcastNotification;
use App\Entity\UserManager;
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
   *
   * @return Response
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function sendAction(Request $request)
  {
    $message = $request->get("Message");
    $title = $request->get("Title");

    $notification_service = $this->get("catro_notification_service");

    $notification_service->addNotifications($this->getNotifications($message, $title));

    return new Response("OK");
  }


  /**
   * @param $message
   * @param $title
   *
   * @return \Generator
   */
  private function getNotifications($message, $title)
  {
    /**
     * @var UserManager $usermanager
     */
    $usermanager = $this->get('usermanager');
    foreach ($usermanager->findAll() as $user)
    {
      yield new BroadcastNotification($user, $title, $message);
    }
  }

}