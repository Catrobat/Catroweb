<?php

namespace App\Security\Authentication;

use Exception;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JWTAuthenticationSuccessListener
{
  private AttachRefreshTokenOnSuccessListener $listener;
  private CookieService $cookie_service;

  public function __construct(AttachRefreshTokenOnSuccessListener $listener, CookieService $cookie_service)
  {
    $this->listener = $listener;
    $this->cookie_service = $cookie_service;
  }

  /**
   * Sets JWT as a cookie on successful authentication.
   *
   * @throws Exception
   */
  public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
  {
    $this->listener->attachRefreshToken($event);
    $event->getResponse()->headers->setCookie($this->cookie_service->createRefreshTokenCookie($event->getData()['refresh_token']));
    $event->getResponse()->headers->setCookie($this->cookie_service->createBearerTokenCookie($event->getData()['token']));
  }
}
