<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\RouterInterface;

class FlavorListener
{
    private $router;
    
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    
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
        
        $context = $this->router->getContext();
        if (!$context->hasParameter('flavor'))
        {
            $context->setParameter('flavor', $attributes->get('flavor'));
        }
    }
}
