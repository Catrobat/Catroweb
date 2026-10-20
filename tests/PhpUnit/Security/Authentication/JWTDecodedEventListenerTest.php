<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\DB\Entity\User\User;
use App\Security\Authentication\JWTDecodedEventListener;
use App\User\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(JWTDecodedEventListener::class)]
final class JWTDecodedEventListenerTest extends TestCase
{
  /**
   * @param array<string, mixed> $payload
   */
  #[Group('unit')]
  #[DataProvider('provideTokens')]
  public function testRejectsTokensIssuedBeforeTheCredentialsChanged(?int $changed_at, array $payload, bool $valid): void
  {
    $user = new User();
    if (null !== $changed_at) {
      $reflection = new \ReflectionProperty(User::class, 'credentials_changed_at');
      $reflection->setValue($user, new \DateTime('@'.$changed_at));
    }

    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findOneBy')->willReturnCallback(
      static fn (array $criteria): ?User => 'u' === $criteria['username'] ? $user : null
    );

    $event = new JWTDecodedEvent($payload);
    new JWTDecodedEventListener($user_manager)($event);

    $this->assertSame($valid, $event->isValid());
  }

  /**
   * @return array<string, array{?int, array<string, mixed>, bool}>
   */
  public static function provideTokens(): array
  {
    $changed = 1_800_000_000;

    return [
      'credentials never changed' => [null, ['username' => 'u', 'iat' => $changed - 60], true],
      'issued after the change' => [$changed, ['username' => 'u', 'iat' => $changed + 60], true],
      'issued in the second of the change' => [$changed, ['username' => 'u', 'iat' => $changed], true],
      'issued before the change' => [$changed, ['username' => 'u', 'iat' => $changed - 1], false],
      'no iat' => [$changed, ['username' => 'u'], false],
      'unknown user' => [$changed, ['username' => 'unknown', 'iat' => $changed - 60], true],
    ];
  }

  #[Group('unit')]
  public function testChangingThePasswordMarksTheCredentialsChanged(): void
  {
    $user = new User();
    $user->setPassword('hash-1');
    $this->assertNull($user->getCredentialsChangedAt(), 'the first password is no change');

    $user->setPassword('hash-1');
    $this->assertNull($user->getCredentialsChangedAt(), 'the same hash is no change');

    $user->setPassword('hash-2');
    $this->assertNotNull($user->getCredentialsChangedAt());
  }
}
