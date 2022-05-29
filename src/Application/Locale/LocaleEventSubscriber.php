<?php

namespace App\Application\Locale;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleEventSubscriber implements EventSubscriberInterface
{
  public function onKernelRequest(RequestEvent $event): void
  {
    $event->getRequest()->setLocale(
        (string) ($event->getRequest()->cookies->get('hl') ?? $event->getRequest()->getPreferredLanguage() ?? 'en')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
  }
}
