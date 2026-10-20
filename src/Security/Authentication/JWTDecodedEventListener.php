<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Access tokens are stateless: one issued before a password change, account claim or ban would stay valid
 * until it expires. Reject every token issued before the user's credentials last changed.
 */
#[AsEventListener(event: Events::JWT_DECODED)]
class JWTDecodedEventListener
{
  public function __construct(
    private readonly UserManager $user_manager,
  ) {
  }

  public function __invoke(JWTDecodedEvent $event): void
  {
    $payload = $event->getPayload();
    $username = $payload['username'] ?? null;
    if (!\is_string($username)) {
      return;
    }

    $user = $this->user_manager->findOneBy(['username' => $username]);
    $changed_at = $user instanceof User ? $user->getCredentialsChangedAt() : null;
    if (null === $changed_at) {
      return;
    }

    // ponytail: "iat" has second precision, so a token issued in the same second as the change survives.
    $issued_at = $payload['iat'] ?? null;
    if (!is_numeric($issued_at) || (int) $issued_at < $changed_at->getTimestamp()) {
      $event->markAsInvalid();
    }
  }
}
