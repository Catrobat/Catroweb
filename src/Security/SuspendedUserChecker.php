<?php

declare(strict_types=1);

namespace App\Security;

use App\DB\Entity\User\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SuspendedUserChecker implements UserCheckerInterface
{
  public const string MESSAGE_KEY = 'error.account.suspended';
  public const string ERROR_CODE = 'account_suspended';

  #[\Override]
  public function checkPreAuth(UserInterface $user): void
  {
    if ($user instanceof User && $user->getProfileHidden()) {
      throw new CustomUserMessageAccountStatusException(self::MESSAGE_KEY);
    }
  }

  #[\Override]
  public function checkPostAuth(UserInterface $user): void
  {
  }
}
