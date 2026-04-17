<?php

declare(strict_types=1);

namespace App\Security;

use App\DB\Entity\User\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SuspendedUserChecker implements UserCheckerInterface
{
  public const string MESSAGE_KEY = 'error.account.suspended';
  public const string ERROR_CODE = 'account_suspended';

  public function __construct(
    private readonly RequestStack $request_stack,
  ) {
  }

  #[\Override]
  public function checkPreAuth(UserInterface $user): void
  {
    if (!$user instanceof User || !$user->getProfileHidden()) {
      return;
    }

    // Web form login (X-Auth-Mode: cookie) is allowed so suspended users
    // can still access their profile and submit appeals.
    $request = $this->request_stack->getCurrentRequest();
    if (null !== $request && 'cookie' === $request->headers->get('X-Auth-Mode')) {
      return;
    }

    throw new CustomUserMessageAccountStatusException(self::MESSAGE_KEY);
  }

  #[\Override]
  public function checkPostAuth(UserInterface $user): void
  {
  }
}
