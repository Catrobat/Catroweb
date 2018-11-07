<?php

namespace Catrobat\AppBundle\Controller\Admin;


use Catrobat\AppBundle\Entity\BroadcastNotification;
use Catrobat\AppBundle\Entity\UserManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BroadcastNotificationController
 * @package Catrobat\AppBundle\Controller\Admin
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