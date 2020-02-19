<?php

namespace App\Catrobat\Listeners;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class LanguageListener.
 */
class LanguageListener
{
  public function onKernelRequest(RequestEvent $event)
  {
    $pref_language = $event->getRequest()->cookies->get('hl');
    if (null === $pref_language)
    {
      $pref_language = $event->getRequest()->getPreferredLanguage();
    }
    if (null === $pref_language)
    {
      $pref_language = 'en';
    }
    $event->getRequest()->setLocale($pref_language);
  }
}
