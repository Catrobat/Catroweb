<?php

declare(strict_types=1);

namespace App\Api\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The OpenAPI `Limit` schema only has a minimum, and several list endpoints pass the client's limit
 * straight to the database, so `?limit=200000` loads every row. Cap it for every API route here.
 *
 * The cap is high on purpose: the app asks for up to 999 of a user's projects in one call.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
final class ListLimitCapListener
{
  public const int MAX_LIMIT = 1000;

  public function __invoke(RequestEvent $event): void
  {
    $request = $event->getRequest();
    if (!str_starts_with($request->getPathInfo(), '/api/')) {
      return;
    }

    // all() instead of get(): get() throws on ?limit[]=…, which the API validation should answer.
    $limit = $request->query->all()['limit'] ?? null;
    // Only plain integers; anything else is left to the API validation (400). The length check comes first
    // because a value past PHP_INT_MAX would not survive the (int) cast.
    if (is_string($limit) && ctype_digit($limit) && (strlen(ltrim($limit, '0')) > 4 || (int) $limit > self::MAX_LIMIT)) {
      $request->query->set('limit', (string) self::MAX_LIMIT);
    }
  }
}
