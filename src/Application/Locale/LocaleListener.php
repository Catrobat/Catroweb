<?php

namespace App\Application\Locale;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
  public function onKernelRequest(RequestEvent $event): void
  {
    $event->getRequest()->setLocale(
        $event->getRequest()->cookies->get('hl') ?? $event->getRequest()->getPreferredLanguage() ?? 'en'
    );
  }
}
