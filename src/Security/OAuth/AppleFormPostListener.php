<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sign in with Apple returns via a cross-site form POST (response_mode=form_post). Browsers do not
 * send the SameSite=Lax session cookie on that POST, so HWIOAuth cannot find the CSRF state it
 * stored in the session and rejects the login. Re-submit the same POST once from our own origin,
 * where the session cookie is sent.
 *
 * Runs after the session listener (128) and before the firewall (8).
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 64)]
class AppleFormPostListener
{
  public const string CHECK_PATH = '/login/check-apple';

  private const string RESUBMITTED = '_resubmitted';

  public function __invoke(RequestEvent $event): void
  {
    $request = $event->getRequest();
    if (!$event->isMainRequest()
      || !$request->isMethod('POST')
      || self::CHECK_PATH !== $request->getPathInfo()
      || $request->hasPreviousSession()
      || $request->request->has(self::RESUBMITTED)) {
      return;
    }

    $fields = '';
    foreach ($request->request->all() + [self::RESUBMITTED => '1'] as $name => $value) {
      if (is_string($value)) {
        $fields .= sprintf('<input type="hidden" name="%s" value="%s">', self::escape((string) $name), self::escape($value));
      }
    }

    $event->setResponse(new Response(
      sprintf(
        '<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex"></head><body>'.
        '<form method="post" action="%s">%s<noscript><button type="submit">Continue</button></noscript></form>'.
        '<script>document.forms[0].submit()</script></body></html>',
        self::escape($request->getBaseUrl().self::CHECK_PATH),
        $fields
      ),
      Response::HTTP_OK,
      ['Cache-Control' => 'no-store', 'Referrer-Policy' => 'no-referrer']
    ));
  }

  private static function escape(string $value): string
  {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }
}
