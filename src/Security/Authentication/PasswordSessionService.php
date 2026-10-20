<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

/** Serializes credential changes and refresh issuance on the account row. */
class PasswordSessionService
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly RefreshTokenManagerInterface $refresh_manager,
    private readonly RefreshTokenGeneratorInterface $generator,
    private readonly JWTTokenManagerInterface $jwt_manager,
    private readonly AccountSessionCoordinator $coordinator,
    #[Autowire('%env(int:REFRESH_TOKEN_TTL)%')]
    private readonly int $refresh_ttl,
  ) {
  }

  /**
   * @return array{bearer: string, refresh: string}|null
   */
  public function changePassword(User $user, ?Request $request, callable $update): ?array
  {
    return $this->rotateOnAccountChange($user, $request, $update, true);
  }

  /** @return array{bearer: string, refresh: string}|null */
  public function changeUsername(User $user, ?Request $request, callable $update): ?array
  {
    return $this->rotateOnAccountChange($user, $request, $update, false);
  }

  /** @return array{bearer: string, refresh: string}|null */
  private function rotateOnAccountChange(User $user, ?Request $request, callable $update, bool $require_cookie_proof): ?array
  {
    return $this->coordinator->withVerifiedAccountUpdate($user, function () use ($user, $request, $update, $require_cookie_proof): ?array {
      $old_username = $user->getUserIdentifier();
      $cookie = $request?->cookies->get('REFRESH_TOKEN');
      $proof = \is_string($cookie) && '' !== $cookie ? $this->refresh_manager->get($cookie) : null;
      $preserve_current = $proof instanceof RefreshTokenInterface && $this->coordinator->isCurrentProof($user, $proof);
      $this->revokeAll($old_username);
      $update();
      // A combined username/password change must also remove tokens under the resulting name.
      if ($old_username !== $user->getUserIdentifier()) {
        $this->revokeAll($user->getUserIdentifier());
      }
      if ($require_cookie_proof && !$preserve_current) {
        return null;
      }
      $replacement = $this->generator->createForUserWithTtl($user, $this->refresh_ttl);
      // A hashing manager may replace the model's plain value on save.
      $refresh = $replacement->getRefreshToken();
      if (null === $refresh || '' === $refresh) {
        throw new \LogicException('A replacement refresh token must have a value.');
      }
      $this->refresh_manager->save($replacement);

      return [
        // jti guarantees replacement even when the old and new JWT share an iat second.
        'bearer' => $this->jwt_manager->createFromPayload($user, ['jti' => bin2hex(random_bytes(16))]),
        'refresh' => $refresh,
      ];
    });
  }

  public function resetPassword(User $user, string $hash, callable $consume_reset): void
  {
    $exception = $this->coordinator->withLockedUser($user, function () use ($user, $hash, $consume_reset): ?ResetPasswordExceptionInterface {
      try {
        // Expected invalid/expired proof rejection is read-only and must leave the EntityManager usable.
        $consume_reset();
      } catch (ResetPasswordExceptionInterface $exception) {
        return $exception;
      }
      $user->setPassword($hash);
      $this->revokeAll($user->getUserIdentifier());

      return null;
    });
    if (null !== $exception) {
      throw $exception;
    }
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
