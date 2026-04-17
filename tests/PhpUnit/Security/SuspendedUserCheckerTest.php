<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\DB\Entity\User\User;
use App\Security\SuspendedUserChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[CoversClass(SuspendedUserChecker::class)]
final class SuspendedUserCheckerTest extends TestCase
{
  private SuspendedUserChecker $checker;

  protected function setUp(): void
  {
    $this->checker = new SuspendedUserChecker();
  }

  #[Group('unit')]
  public function testSuspendedUserThrowsException(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->expectException(CustomUserMessageAccountStatusException::class);
    $this->expectExceptionMessage('error.account.suspended');

    $this->checker->checkPreAuth($user);
  }

  #[Group('unit')]
  public function testActiveUserPassesWithoutException(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(false);

    $this->checker->checkPreAuth($user);

    $this->addToAssertionCount(1);
  }

  #[Group('unit')]
  public function testNonUserInterfaceImplementationPassesWithoutException(): void
  {
    $user = $this->createStub(UserInterface::class);

    $this->checker->checkPreAuth($user);

    $this->addToAssertionCount(1);
  }

  #[Group('unit')]
  public function testCheckPostAuthDoesNothing(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->checker->checkPostAuth($user);

    $this->addToAssertionCount(1);
  }
}
