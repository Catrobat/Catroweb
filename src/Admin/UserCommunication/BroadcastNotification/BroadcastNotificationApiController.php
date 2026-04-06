<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BroadcastNotificationApiController extends AbstractController
{
  private const int BATCH_SIZE = 100;

  public function __construct(
    private readonly EntityManagerInterface $entityManager,
  ) {
  }

  #[Route('/broadcast-notification/send', name: 'admin_broadcast_notification_send', methods: ['POST'])]
  #[IsGranted('ROLE_ADMIN')]
  public function send(Request $request): JsonResponse
  {
    $data = $request->toArray();
    $message = trim((string) ($data['message'] ?? ''));

    if ('' === $message) {
      return $this->json(['error' => 'Message must not be empty.'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $count = 0;
    $query = $this->entityManager->createQuery('SELECT u FROM '.User::class.' u');

    foreach ($query->toIterable() as $user) {
      $this->entityManager->persist(new BroadcastNotification($user, '', $message));
      ++$count;

      if (0 === $count % self::BATCH_SIZE) {
        $this->entityManager->flush();
        $this->entityManager->clear();
      }
    }

    if (0 !== $count % self::BATCH_SIZE) {
      $this->entityManager->flush();
      $this->entityManager->clear();
    }

    return $this->json(['count' => $count]);
  }
}
