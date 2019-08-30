<?php

namespace App\Catrobat\Listeners;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;


/**
 * Class LanguageListener
 * @package App\Catrobat\Listeners
 */
class LanguageListener
{
  /**
   * @param GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event)
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
