<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\Security\SuspendedUserChecker;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_failure')]
class SuspensionAuthenticationFailureEventListener
{
  public function __invoke(AuthenticationFailureEvent $event): void
  {
    $exception = $event->getException();
    if (!$exception instanceof CustomUserMessageAccountStatusException) {
      return;
    }

    if (SuspendedUserChecker::MESSAGE_KEY !== $exception->getMessageKey()) {
      return;
    }

    $event->setResponse(new JsonResponse(
      ['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Account suspended.', 'error_code' => SuspendedUserChecker::ERROR_CODE],
      Response::HTTP_UNAUTHORIZED
    ));
  }
}
