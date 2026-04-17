<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\SuspensionAuthenticationFailureEventListener;
use App\Security\SuspendedUserChecker;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * @internal
 */
#[CoversClass(SuspensionAuthenticationFailureEventListener::class)]
final class SuspensionAuthenticationFailureEventListenerTest extends TestCase
{
  private SuspensionAuthenticationFailureEventListener $listener;

  #[\Override]
  protected function setUp(): void
  {
    $this->listener = new SuspensionAuthenticationFailureEventListener();
  }

  #[Group('unit')]
  public function testSuspensionExceptionEnrichesResponseWithErrorCode(): void
  {
    $exception = new CustomUserMessageAccountStatusException(SuspendedUserChecker::MESSAGE_KEY);
    $originalResponse = new JWTAuthenticationFailureResponse();
    $event = new AuthenticationFailureEvent($exception, $originalResponse);

    ($this->listener)($event);

    $response = $event->getResponse();
    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode((string) $response->getContent(), true);
    $this->assertSame(SuspendedUserChecker::ERROR_CODE, $data['error_code']);
    $this->assertSame(401, $data['code']);
  }

  #[Group('unit')]
  public function testOtherAuthenticationExceptionsAreNotModified(): void
  {
    $exception = new AuthenticationException('Bad credentials.');
    $originalResponse = new JWTAuthenticationFailureResponse();
    $event = new AuthenticationFailureEvent($exception, $originalResponse);

    ($this->listener)($event);

    $this->assertSame($originalResponse, $event->getResponse());
  }

  #[Group('unit')]
  public function testDifferentMessageKeyIsNotModified(): void
  {
    $exception = new CustomUserMessageAccountStatusException('error.email.not.verified');
    $originalResponse = new JWTAuthenticationFailureResponse();
    $event = new AuthenticationFailureEvent($exception, $originalResponse);

    ($this->listener)($event);

    $this->assertSame($originalResponse, $event->getResponse());
  }
}
