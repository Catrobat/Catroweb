<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\User;
use App\User\Notification\EmailNotificationPreference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationPreferenceController extends AbstractController
{
  public function __construct(
    private readonly EntityManagerInterface $em,
  ) {
  }

  #[Route(
    path: '/notification-preference',
    name: 'notification_preference_update',
    methods: ['POST'],
  )]
  public function update(Request $request): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user instanceof User) {
      return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    $preference = $request->request->get('preference');
    $enumValue = EmailNotificationPreference::tryFrom((string) $preference);

    if (null === $enumValue) {
      return new JsonResponse(['error' => 'Invalid preference'], Response::HTTP_BAD_REQUEST);
    }

    $user->setEmailNotificationPreference($enumValue);
    $this->em->flush();

    return new JsonResponse(['status' => 'ok']);
  }
}
