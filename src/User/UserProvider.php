<?php

declare(strict_types=1);

namespace App\User;

use App\DB\Entity\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @phpstan-implements UserProviderInterface<User>
 */
class UserProvider implements UserProviderInterface
{
  public function __construct(private UserManager $userManager)
  {
  }

  public function loadUserByUsername(string $username): User
  {
    return $this->loadUserByIdentifier($username);
  }

  public function loadUserByIdentifier(string $identifier): User
  {
    $user = $this->findUser($identifier);

    if (null === $user || !$user->isEnabled()) {
      throw new UserNotFoundException(sprintf('Username "%s" does not exist.', $identifier));
    }

    return $user;
  }

  public function refreshUser(UserInterface $user): User
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', User::class, $user::class));
    }

    if (null === $reloadedUser = $this->userManager->findOneBy(['id' => $user->getId()])) {
      throw new UserNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId() ?? ''));
    }

    return $reloadedUser;
  }

  private function findUser(string $username): ?User
  {
    return $this->userManager->findUserByUsernameOrEmail($username);
  }

  public function supportsClass(string $class): bool
  {
    return User::class === $class;
  }
}
