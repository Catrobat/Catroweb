<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<BroadcastNotification>
 */
class BroadcastNotificationController extends CRUDController
{
  #[\Override]
  public function listAction(Request $request): Response
  {
    return $this->render('Admin/UserCommunication/BroadcastNotification.html.twig');
  }
}
