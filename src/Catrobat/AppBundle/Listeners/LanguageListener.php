<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class LanguageListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $cookie_language = $event->getRequest()->cookies->get("hl");
        $event->getRequest()->setLocale($cookie_language);
    }
}