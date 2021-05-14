<?php

namespace App\Catrobat\Listeners;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class JWTTokenListener
{
  public function onKernelRequest(RequestEvent $event): void
  {
    if($event->getRequest()->cookies->has('BEARER')) {
      $bearer_token = $event->getRequest()->cookies->get('BEARER');
      $event->getRequest()->headers->set("Authorization", "Bearer " . $bearer_token);
    }

    if($event->getRequest()->cookies->has('REFRESH_TOKEN')) {
      $x_refresh = $event->getRequest()->cookies->get('REFRESH_TOKEN');
      $event->getRequest()->headers->set("X-Refresh", $x_refresh);
    }
  }
}
