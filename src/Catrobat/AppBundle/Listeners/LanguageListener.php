<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;


/**
 * Class LanguageListener
 * @package Catrobat\AppBundle\Listeners
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
    $event->getRequest()->setLocale($pref_language);
  }
}
