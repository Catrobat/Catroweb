<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use App\Security\SuspendedUserChecker;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/** Coordinates authentication issuance and credential writes on the account row. */
class AccountSessionCoordinator
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly SuspendedUserChecker $suspended_user_checker,
  ) {
  }

  /**
   * @template TResult
   *
   * @param callable(): TResult $issue
   *
   * @return TResult
   */
  public function withVerifiedLogin(User $user, callable $issue): mixed
  {
    return $this->withUnchangedAuthenticatedAccount($user, function () use ($user, $issue): mixed {
      // Preserve the established cookie-mode sign-in exception for suspended users' appeals.
      $this->suspended_user_checker->checkPreAuth($user);

      return $issue();
    });
  }

  /**
   * @template TResult
   *
   * @param callable(): TResult $update
   *
   * @return TResult
   */
  public function withVerifiedAccountUpdate(User $user, callable $update): mixed
  {
    // /api/users/me PATCH is intentionally permitted for an already suspended actor.
    return $this->withUnchangedAuthenticatedAccount($user, $update);
  }

  /**
   * @template TResult
   *
   * @param callable(): TResult $operation
   *
   * @return TResult
   */
  private function withUnchangedAuthenticatedAccount(User $user, callable $operation): mixed
  {
    $authenticated_hash = $user->getPassword();
    $authenticated_identifier = $user->getUserIdentifier();
    $was_suspended = $user->getProfileHidden();
    $result = $this->withLockedUser($user, function () use ($user, $authenticated_hash, $authenticated_identifier, $was_suspended, $operation): array {
      if ((!$was_suspended && $user->getProfileHidden()) || $authenticated_hash !== $user->getPassword() || $authenticated_identifier !== $user->getUserIdentifier()) {
        return ['valid' => false];
      }

      return ['valid' => true, 'value' => $operation()];
    });
    if (!\array_key_exists('value', $result)) {
      throw new BadCredentialsException('Credentials, identifier or account status changed during authentication. Sign in again.');
    }

    return $result['value'];
  }

  /**
   * @template TResult
   *
   * @param callable(): TResult $issue
   *
   * @return TResult
   */
  public function withVerifiedRefresh(User $user, RefreshTokenInterface $proof, callable $issue): mixed
  {
    $result = $this->withLockedUser($user, function () use ($user, $proof, $issue): array {
      // Recheck the database after obtaining the account lock: authentication may have raced a reset.
      if ($user->getProfileHidden() || !$this->isCurrentProof($user, $proof)) {
        return ['valid' => false];
      }

      return ['valid' => true, 'value' => $issue()];
    });
    // An ordinary rejected cookie must not close Doctrine's EntityManager for the rest of the request.
    if (!\array_key_exists('value', $result)) {
      throw new BadCredentialsException('The refresh session is no longer valid.');
    }

    return $result['value'];
  }

  public function isCurrentProof(User $user, RefreshTokenInterface $proof): bool
  {
    if (!$proof->isValid() || $proof->getUsername() !== $user->getUserIdentifier()) {
      return false;
    }

    return null !== $this->entity_manager->createQueryBuilder()
      ->select('t.id')
      ->from(RefreshToken::class, 't')
      ->where('t.refreshToken = :token')
      ->andWhere('t.username = :username')
      ->setParameter('token', $proof->getRefreshToken())
      ->setParameter('username', $user->getUserIdentifier())
      ->setMaxResults(1)
      ->getQuery()
      ->getOneOrNullResult()
    ;
  }

  /**
   * @template TResult
   *
   * @param callable(): TResult $operation
   *
   * @return TResult
   */
  public function withLockedUser(User $user, callable $operation): mixed
  {
    return $this->entity_manager->wrapInTransaction(function () use ($user, $operation): mixed {
      $this->entity_manager->refresh($user, LockMode::PESSIMISTIC_WRITE);

      return $operation();
    });
  }
}
