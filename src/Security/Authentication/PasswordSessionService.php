<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/** Revokes refresh sessions when an account's password or username changes. */
class PasswordSessionService
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly RefreshTokenManagerInterface $refresh_manager,
    private readonly RefreshTokenGeneratorInterface $generator,
    private readonly JWTTokenManagerInterface $jwt_manager,
    #[Autowire('%env(int:REFRESH_TOKEN_TTL)%')]
    private readonly int $refresh_ttl,
  ) {
  }

  /**
   * A password change revokes every session; only a caller with a valid refresh cookie gets a new one.
   * A rename revokes the old name's sessions. Other profile saves keep all sessions.
   *
   * @return array{bearer: string, refresh: string}|null null when the caller must sign in again
   */
  public function updateAccount(User $user, ?Request $request, bool $password_change, callable $update): ?array
  {
    return $this->entity_manager->wrapInTransaction(function () use ($user, $request, $password_change, $update): ?array {
      $old_username = $user->getUserIdentifier();
      $cookie = $request?->cookies->get('REFRESH_TOKEN');
      $proof = \is_string($cookie) && '' !== $cookie ? $this->refresh_manager->get($cookie) : null;
      $keep_session = !$password_change || (null !== $proof && $proof->isValid() && $proof->getUsername() === $old_username);

      $update();
      if ($password_change || $old_username !== $user->getUserIdentifier()) {
        $this->revokeAll($old_username);
        $this->revokeAll($user->getUserIdentifier());
      }
      if (!$keep_session) {
        return null;
      }

      $replacement = $this->generator->createForUserWithTtl($user, $this->refresh_ttl);
      // Read the plain value before save: a hashing manager may replace it.
      $refresh = (string) $replacement->getRefreshToken();
      $this->refresh_manager->save($replacement);

      return [
        // jti makes the new JWT differ from the old one even within the same iat second.
        'bearer' => $this->jwt_manager->createFromPayload($user, ['jti' => bin2hex(random_bytes(16))]),
        'refresh' => $refresh,
      ];
    });
  }

  public function resetPassword(User $user, string $hash): void
  {
    $user->setPassword($hash);
    $this->revokeAll($user->getUserIdentifier());
    $this->entity_manager->flush();
  }

  private function revokeAll(string $username): void
  {
    $this->entity_manager->createQueryBuilder()
      ->delete(RefreshToken::class, 't')
      ->where('t.username = :username')
      ->setParameter('username', $username)
      ->getQuery()
      ->execute()
    ;
  }
}
