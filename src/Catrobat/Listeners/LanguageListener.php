<?php

namespace App\Catrobat\Listeners;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class LanguageListener
 * @package App\Catrobat\Listeners
 */
class LanguageListener
{
  /**
   * @param RequestEvent $event
   */
  public function onKernelRequest(RequestEvent $event)
  {
    $pref_language = $event->getRequest()->cookies->get('hl');
    if ($pref_language === null)
    {
      $pref_language = $event->getRequest()->getPreferredLanguage();
    }
    if ($pref_language === null) {
      $pref_language = "en";
    }
    $event->getRequest()->setLocale($pref_language);
  }
}
