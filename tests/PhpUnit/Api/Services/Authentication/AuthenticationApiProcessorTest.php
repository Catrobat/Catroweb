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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

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

    $processor = $this->appleProcessor($user_manager, $payload);

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
    $user_manager = $this->userManagerFindingByEmail($existing);
    $user_manager->expects($this->once())->method('claimUnverifiedAccountForOAuth')->with($existing, true);
    $user_manager->expects($this->once())->method('updateUser')->with($existing);

    $processor = $this->appleProcessor($user_manager, ['id' => '001234.abcd', 'email' => 'someone@example.com', 'email_verified' => 'true']);

    $this->assertSame($existing, $processor->connectUserToAccount('token', 'apple'));
    $this->assertSame('001234.abcd', $existing->getAppleId());
  }

  public function testRefusesToLinkWhenTheAccountCannotBeClaimed(): void
  {
    $existing = new User();
    $user_manager = $this->userManagerFindingByEmail($existing);
    $user_manager->expects($this->once())->method('claimUnverifiedAccountForOAuth')->with($existing, false)
      ->willThrowException(new CustomUserMessageAccountStatusException('not verified'))
    ;
    $user_manager->expects($this->never())->method('updateUser');

    $processor = $this->appleProcessor($user_manager, ['id' => '001234.abcd', 'email' => 'someone@example.com']);

    try {
      $processor->connectUserToAccount('token', 'apple');
      $this->fail('linking must be refused');
    } catch (HttpException $e) {
      $this->assertSame(401, $e->getStatusCode());
    }

    $this->assertNull($existing->getAppleId());
  }

  private function userManagerFindingByEmail(User $user): UserManager&MockObject
  {
    $user_manager = $this->createMock(UserManager::class);
    $user_manager->method('findOneBy')->willReturn(null);
    $user_manager->method('findUserByEmail')->willReturn($user);

    return $user_manager;
  }

  /**
   * @param array<string, mixed> $payload
   */
  private function appleProcessor(UserManager $user_manager, array $payload): AuthenticationApiProcessor
  {
    return new class($payload, $user_manager, $this->createStub(AuthenticationManager::class), $this->createStub(AchievementManager::class)) extends AuthenticationApiProcessor {
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
  }
}
