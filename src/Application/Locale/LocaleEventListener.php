<?php

declare(strict_types=1);

namespace App\Application\Locale;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 100)]
class LocaleEventListener
{
  public function onKernelRequest(RequestEvent $event): void
  {
    $event->getRequest()->setLocale(
      (string) ($event->getRequest()->cookies->get('hl') ?? $event->getRequest()->getPreferredLanguage() ?? 'en')
    );
  }
}
