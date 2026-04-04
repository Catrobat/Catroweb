<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\User;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<BroadcastNotification>
 */
class BroadcastNotificationController extends CRUDController
{
  private const int BATCH_SIZE = 100;

  public function __construct(
    protected NotificationManager $notification_manager,
    protected EntityManagerInterface $entity_manager,
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    return $this->render('Admin/UserCommunication/BroadcastNotification.html.twig');
  }

  public function sendAction(Request $request): Response
  {
    $message = (string) $request->request->get('Message', '');
    if ('' === $message) {
      return new Response('Message must not be empty', Response::HTTP_BAD_REQUEST);
    }

    $title = '';
    $count = 0;

    $query = $this->entity_manager->createQuery('SELECT u FROM '.User::class.' u');
    foreach ($query->toIterable() as $user) {
      $notification = new BroadcastNotification($user, $title, $message);
      $this->entity_manager->persist($notification);
      ++$count;

      if (0 === $count % self::BATCH_SIZE) {
        $this->entity_manager->flush();
        $this->entity_manager->clear();
      }
    }

    if (0 !== $count % self::BATCH_SIZE) {
      $this->entity_manager->flush();
      $this->entity_manager->clear();
    }

    return new Response('OK - '.$count.' notifications sent');
  }
}
