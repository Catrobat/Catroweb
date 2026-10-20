<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\OAuth;

use App\DB\Entity\User\User;
use App\Security\OAuth\HwiOauthAccountConnector;
use App\User\UserManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(HwiOauthAccountConnector::class)]
final class HwiOauthAccountConnectorTest extends TestCase
{
  public function testConnectingMovesTheProviderIdentityAwayFromThePreviousAccount(): void
  {
    $previous = new User();
    $previous->setGoogleId('google-1');
    $previous->setGoogleAccessToken('old-token');
    $current = new User();

    $owner = $this->createStub(ResourceOwnerInterface::class);
    $owner->method('getName')->willReturn('google');
    $response = $this->createStub(UserResponseInterface::class);
    $response->method('getResourceOwner')->willReturn($owner);
    $response->method('getUsername')->willReturn('google-1');
    $response->method('getAccessToken')->willReturn('new-token');

    $saved = [];
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findOneBy')->willReturnCallback(static fn (array $criteria): ?User => ['google_id' => 'google-1'] === $criteria ? $previous : null);
    $user_manager->method('updateUser')->willReturnCallback(static function (User $user) use (&$saved): void {
      $saved[] = $user;
    });

    new HwiOauthAccountConnector($user_manager)->connect($current, $response);

    $this->assertNull($previous->getGoogleId());
    $this->assertNull($previous->getGoogleAccessToken());
    $this->assertSame('google-1', $current->getGoogleId());
    $this->assertSame([$previous, $current], $saved);
  }
}
