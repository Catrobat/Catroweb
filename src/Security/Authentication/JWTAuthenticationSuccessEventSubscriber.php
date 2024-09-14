<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JWTAuthenticationSuccessEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected EntityManagerInterface $entityManager, protected UserProviderInterface $userProvider)
  {
  }

  public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
  {
    $user = $event->getUser();

    if ($user instanceof User) {
      $user->setLastLogin(TimeUtils::getDateTime());
      $this->entityManager->flush();
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [
      'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
    ];
  }
}
