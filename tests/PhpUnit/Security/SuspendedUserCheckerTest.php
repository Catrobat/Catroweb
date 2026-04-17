<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\DB\Entity\User\User;
use App\Security\SuspendedUserChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[CoversClass(SuspendedUserChecker::class)]
final class SuspendedUserCheckerTest extends TestCase
{
  private SuspendedUserChecker $checker;

  private RequestStack $request_stack;

  #[\Override]
  protected function setUp(): void
  {
    $this->request_stack = new RequestStack();
    $this->checker = new SuspendedUserChecker($this->request_stack);
  }

  #[Group('unit')]
  public function testSuspendedUserBlockedOnApiLogin(): void
  {
    $request = Request::create('/api/authentication', 'POST');
    $this->request_stack->push($request);

    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->expectException(CustomUserMessageAccountStatusException::class);
    $this->expectExceptionMessage(SuspendedUserChecker::MESSAGE_KEY);

    $this->checker->checkPreAuth($user);
  }

  #[Group('unit')]
  public function testSuspendedUserAllowedOnWebLogin(): void
  {
    $this->expectNotToPerformAssertions();

    $request = Request::create('/api/authentication', 'POST');
    $request->headers->set('X-Auth-Mode', 'cookie');
    $this->request_stack->push($request);

    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->checker->checkPreAuth($user);
  }

  #[Group('unit')]
  public function testActiveUserPassesWithoutException(): void
  {
    $this->expectNotToPerformAssertions();

    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(false);

    $this->checker->checkPreAuth($user);
  }

  #[Group('unit')]
  public function testNonUserInterfaceImplementationPassesWithoutException(): void
  {
    $this->expectNotToPerformAssertions();

    $user = $this->createStub(UserInterface::class);

    $this->checker->checkPreAuth($user);
  }

  #[Group('unit')]
  public function testCheckPostAuthDoesNothing(): void
  {
    $this->expectNotToPerformAssertions();

    $user = $this->createStub(User::class);
    $user->method('getProfileHidden')->willReturn(true);

    $this->checker->checkPostAuth($user);
  }
}
