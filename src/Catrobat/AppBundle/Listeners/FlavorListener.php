<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class FlavorListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->attributes->set("kodey", $event->getRequest()->attributes->get('flavor') === "pocketkodey");
        if (!$event->getRequest()->attributes->has('flavor'))
        {
            $event->getRequest()->attributes->set('flavor','pocketcode');
        }
    }
}
