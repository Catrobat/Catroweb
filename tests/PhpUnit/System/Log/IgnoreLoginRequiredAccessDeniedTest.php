<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Log;

use App\DB\Entity\User\User;
use Bugsnag\Client;
use Bugsnag\Report;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Runs reports through the real Bugsnag client's middleware pipeline, so it also proves the callback is registered.
 *
 * @covers \App\System\Log\IgnoreLoginRequiredAccessDenied
 *
 * @internal
 */
class IgnoreLoginRequiredAccessDeniedTest extends KernelTestCase
{
  public function testAccessDeniedForAnonymousCallerIsNotReported(): void
  {
    $this->assertFalse($this->isReported(new AccessDeniedException(), null));
    $this->assertFalse($this->isReported(new AccessDeniedException(), new NullToken()));
  }

  public function testAccessDeniedForLoggedInUserIsReported(): void
  {
    $user = new User();
    $user->setUsername('catrobat');

    $this->assertTrue($this->isReported(new AccessDeniedException(), new UsernamePasswordToken($user, 'main', ['ROLE_USER'])));
  }

  public function testOtherErrorsOfAnonymousCallersAreReported(): void
  {
    $this->assertTrue($this->isReported(new \RuntimeException('boom'), null));
  }

  private function isReported(\Throwable $error, ?TokenInterface $token): bool
  {
    $token_storage = self::getContainer()->get(TokenStorageInterface::class);
    $this->assertInstanceOf(TokenStorageInterface::class, $token_storage);
    $token_storage->setToken($token);

    $client = self::getContainer()->get('bugsnag');
    $this->assertInstanceOf(Client::class, $client);

    $reported = false;
    $client->getPipeline()->execute(
      Report::fromPHPThrowable($client->getConfig(), $error),
      static function () use (&$reported): void {
        $reported = true;
      }
    );

    return $reported;
  }
}
