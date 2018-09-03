<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class FlavorListener
{
  private $router;
  private $theme;

  public function __construct(RouterInterface $router, $theme)
  {
    $this->router = $router;
    $this->theme = $theme;
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
        $attributes->set('flavor', 'pocketcode');
        $session->set('flavor', 'pocketcode');
      }
    }

    $context = $this->router->getContext();
    if (!$context->hasParameter('flavor'))
    {
      $context->setParameter('flavor', $attributes->get('flavor'));
    }
    $this->theme->setName($attributes->get('flavor'));
  }
}
