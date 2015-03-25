<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class FlavorListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        $session = $event->getRequest()->getSession();
        if ($attributes->has('flavor'))
        {
            $session->set('flavor', $attributes->get('flavor'));
        }
        else
        {
            if ($session->has('flavor'))
            {
                $attributes->set('flavor', $session->get('flavor'));
            }
            else
            {
                $attributes->set('flavor','pocketcode');
                $session->set('flavor','pocketcode');
            }
        }
    }
}
