<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\DB\Entity\User\User;
use App\Security\Authentication\LoginFailureLogger;
use App\User\UserManager;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

/**
 * @internal
 */
#[CoversClass(LoginFailureLogger::class)]
final class LoginFailureLoggerTest extends TestCase
{
  private const string PASSWORD = 'hunter2-secret';

  private TestHandler $handler;

  #[\Override]
  protected function setUp(): void
  {
    $this->handler = new TestHandler();
    $this->handler->setFormatter(new LineFormatter(LoginFailureLogger::LOG_FORMAT, null, true, true));
  }

  #[Group('unit')]
  #[DataProvider('provideFailures')]
  public function testLogsBooleansButNeverTheInput(
    string $input, ?string $existing_username, ?string $existing_email,
    bool $looks_like_email, bool $user_exists, ?bool $email_is_registered,
  ): void {
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findUserByUsername')->willReturnCallback(
      fn (string $u): ?User => $u === $existing_username ? $this->createStub(User::class) : null
    );
    $user_manager->method('findUserByEmail')->willReturnCallback(
      fn (string $e): ?User => $e === $existing_email ? $this->createStub(User::class) : null
    );

    $passport = new Passport(new UserBadge($input), new PasswordCredentials(self::PASSWORD));
    $this->dispatch($user_manager, $passport, '/api/authentication', 'api_authentication_login');

    $records = $this->handler->getRecords();
    $this->assertCount(1, $records);
    $expected = [
      'event' => 'login_failed',
      'firewall' => 'api_authentication_login',
      'path' => '/api/authentication',
      'input_looks_like_email' => $looks_like_email,
      'user_exists' => $user_exists,
    ];
    if (null !== $email_is_registered) {
      $expected['email_is_registered'] = $email_is_registered;
    }
    $this->assertSame($expected, $records[0]->context);

    $line = $records[0]->formatted;
    $this->assertStringContainsString('"input_looks_like_email":'.($looks_like_email ? 'true' : 'false'), $line);
    $this->assertStringContainsString('"user_exists":'.($user_exists ? 'true' : 'false'), $line);
    $this->assertStringNotContainsStringIgnoringCase($input, $line);
    $this->assertStringNotContainsString(self::PASSWORD, $line);
    $this->assertStringNotContainsString('127.0.0.1', $line);
  }

  /**
   * @return iterable<string, array{string, ?string, ?string, bool, bool, ?bool}>
   */
  public static function provideFailures(): iterable
  {
    yield 'email of an existing account' => ['Jane.Doe@example.org', null, 'Jane.Doe@example.org', true, false, true];
    yield 'unknown email' => ['nobody@example.org', null, null, true, false, false];
    yield 'username, wrong password' => ['JaneDoe', 'JaneDoe', null, false, true, null];
    yield 'unknown username' => ['NoSuchUser', null, null, false, false, null];
  }

  #[Group('unit')]
  public function testIgnoresFailuresWithoutTypedCredentials(): void
  {
    // e.g. an expired WebView/OAuth token: nothing was typed, so nothing to measure
    $passport = new SelfValidatingPassport(new UserBadge('JaneDoe'));
    $this->dispatch($this->createStub(UserManager::class), $passport, '/app/', 'main');

    $this->assertSame([], $this->handler->getRecords());
  }

  private function dispatch(UserManager $user_manager, Passport $passport, string $path, string $firewall): void
  {
    $listener = new LoginFailureLogger($user_manager, new Logger('login', [$this->handler]));
    $request = Request::create($path, 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']);
    $listener(new LoginFailureEvent(
      new BadCredentialsException(), $this->createStub(AuthenticatorInterface::class), $request, null, $firewall, $passport
    ));
  }
}
