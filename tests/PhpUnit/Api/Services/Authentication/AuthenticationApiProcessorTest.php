<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Authentication;

use App\Api\Services\Authentication\AuthenticationApiProcessor;
use App\Api\Services\AuthenticationManager;
use App\DB\Entity\User\User;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AuthenticationApiProcessor::class)]
class AuthenticationApiProcessorTest extends TestCase
{
  /**
   * @param array<string, mixed> $payload
   */
  #[DataProvider('incompletePayloads')]
  public function testRefusesTokensWithoutUserIdOrEmailBeforeLookingUpAnyAccount(array $payload): void
  {
    $user_manager = $this->createMock(UserManager::class);
    $user_manager->expects($this->never())->method('findOneBy');
    $user_manager->expects($this->never())->method('findUserByEmail');

    $processor = new class($payload, $user_manager, $this->createStub(AuthenticationManager::class), $this->createStub(AchievementManager::class)) extends AuthenticationApiProcessor {
      /**
       * @param array<string, mixed> $payload
       */
      public function __construct(private readonly array $payload, UserManager $user_manager, AuthenticationManager $authentication_manager, AchievementManager $achievement_manager)
      {
        parent::__construct($user_manager, $authentication_manager, $achievement_manager);
      }

      #[\Override]
      protected function getPayloadFromAppleIdToken(string $id_token): array
      {
        return $this->payload;
      }
    };

    $this->expectException(\RuntimeException::class);
    $processor->connectUserToAccount('token', 'apple');
  }

  /**
   * @return array<string, array{array<string, mixed>}>
   */
  public static function incompletePayloads(): array
  {
    return [
      'no id' => [['id' => null, 'email' => 'someone@example.com']],
      'empty id' => [['id' => '', 'email' => 'someone@example.com']],
      'no email' => [['id' => '001234.abcd', 'email' => null]],
    ];
  }

  public function testClaimsTheAccountMatchedByEmailBeforeLinkingIt(): void
  {
    $existing = new User();
    $user_manager = $this->createMock(UserManager::class);
    $user_manager->method('findOneBy')->willReturn(null);
    $user_manager->method('findUserByEmail')->willReturn($existing);
    $user_manager->expects($this->once())->method('claimUnverifiedAccountForOAuth')->with($existing);
    $user_manager->expects($this->once())->method('updateUser')->with($existing);

    $processor = new class($user_manager, $this->createStub(AuthenticationManager::class), $this->createStub(AchievementManager::class)) extends AuthenticationApiProcessor {
      #[\Override]
      protected function getPayloadFromAppleIdToken(string $id_token): array
      {
        return ['id' => '001234.abcd', 'email' => 'someone@example.com'];
      }
    };

    $this->assertSame($existing, $processor->connectUserToAccount('token', 'apple'));
    $this->assertSame('001234.abcd', $existing->getAppleId());
  }
}
