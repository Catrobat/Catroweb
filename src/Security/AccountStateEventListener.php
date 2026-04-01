<?php

declare(strict_types=1);

namespace App\Security;

use App\Api\Exceptions\ApiErrorResponse;
use App\DB\Entity\User\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -10)]
class AccountStateEventListener
{
  /** @var array<array{pattern: string, methods?: list<string>}> */
  private const array EXEMPT_RULES = [
    ['pattern' => '#^/api/authentication#'],
    ['pattern' => '#^/api/user/?$#', 'methods' => ['POST', 'PUT']],
    ['pattern' => '#^/api/user/reset-password#'],
    ['pattern' => '#^/api/(project|comments|user|studio)/[^/]+/appeal$#'],
    ['pattern' => '#^/api/project/[^/]+/reaction#'],
  ];

  public function __construct(
    private readonly TokenStorageInterface $token_storage,
  ) {
  }

  public function __invoke(RequestEvent $event): void
  {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    if (!str_starts_with($path, '/api/')) {
      return;
    }

    $method = $request->getMethod();
    if (\in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
      return;
    }

    foreach (self::EXEMPT_RULES as $rule) {
      if (preg_match($rule['pattern'], $path) && (!isset($rule['methods']) || \in_array($method, $rule['methods'], true))) {
        return;
      }
    }

    $token = $this->token_storage->getToken();
    if (!$token instanceof \Symfony\Component\Security\Core\Authentication\Token\TokenInterface) {
      return;
    }

    $user = $token->getUser();
    if (!$user instanceof User) {
      return;
    }

    if (null === $user->getDateOfBirth()) {
      $event->setResponse(ApiErrorResponse::create(
        Response::HTTP_FORBIDDEN,
        'forbidden',
        'Please complete your registration.'
      ));

      return;
    }

    if (!$user->isVerified()) {
      $event->setResponse(ApiErrorResponse::create(
        Response::HTTP_FORBIDDEN,
        'forbidden',
        'Email verification required.'
      ));

      return;
    }

    if (in_array($user->getConsentStatus(), ['pending', 'revoked'], true)) {
      $event->setResponse(ApiErrorResponse::create(
        Response::HTTP_FORBIDDEN,
        'forbidden',
        'Parental consent is required for users under 14.'
      ));

      return;
    }

    if ($user->getProfileHidden()) {
      $event->setResponse(ApiErrorResponse::create(
        Response::HTTP_FORBIDDEN,
        'forbidden',
        'Your account has been suspended.'
      ));
    }
  }
}
