<?php

declare(strict_types=1);

namespace App\Api\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The OpenAPI `Limit` schema only has a minimum, and several list endpoints pass the client's limit
 * straight to the database, so `?limit=200000` loads every row. Cap it for every generated API route here.
 *
 * The cap is high on purpose: the app asks for up to 999 of a user's projects in one call.
 */
// After the RouterListener (32): the matched route decides, not the raw path, which may be percent-encoded.
#[AsEventListener(event: KernelEvents::REQUEST, priority: 16)]
final class ListLimitCapListener
{
  public const int MAX_LIMIT = 1000;

  public function __invoke(RequestEvent $event): void
  {
    $request = $event->getRequest();
    if (!str_starts_with((string) $request->attributes->get('_route'), 'open_api_server_')) {
      return;
    }

    // all() instead of get(): get() throws on ?limit[]=…, which the API validation should answer.
    $limit = $request->query->all()['limit'] ?? null;
    if (!is_string($limit) || !is_numeric($limit)) {
      return;
    }

    // Mirror the generated controllers: they parse the limit as `$limit + 0` (so "+5000" and " 5000" are
    // 5000) and reject anything that isn't an int. Only ints above the cap need rewriting.
    $value = $limit + 0;
    if (is_int($value) && $value > self::MAX_LIMIT) {
      $request->query->set('limit', (string) self::MAX_LIMIT);
    }
  }
}
