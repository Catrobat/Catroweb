<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success', method: 'updateLastLoginDate')]
class JWTAuthenticationSuccessEventListener
{
  public function __construct(
    protected EntityManagerInterface $entityManager,
    protected UserProviderInterface $userProvider,
  ) {
  }

  public function updateLastLoginDate(AuthenticationSuccessEvent $event): void
  {
    $user = $event->getUser();
    if ($user instanceof User) {
      $user->setLastLogin(TimeUtils::getDateTime());
      $this->entityManager->flush();
    }
  }
}
