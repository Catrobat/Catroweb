<?php

namespace App\Catrobat\Listeners;

use Exception;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;

class JWTAuthenticationSuccessListener
{
    /**
     * @var int
     */
    private int $tokenLifetime;

    /** @var AttachRefreshTokenOnSuccessListener */
    private AttachRefreshTokenOnSuccessListener $listener;

    public function __construct(int $tokenLifetime, AttachRefreshTokenOnSuccessListener $listener)
    {
        $this->tokenLifetime = $tokenLifetime;
        $this->listener = $listener;
    }

    /**
     * Sets JWT as a cookie on successful authentication.
     *
     * @param AuthenticationSuccessEvent $event
     * @throws Exception
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $this->listener->attachRefreshToken($event);
        $event->getResponse()->headers->setCookie(
            new Cookie(
                'REFRESH_TOKEN',
                $event->getData()['refresh_token'],
                time() + $this->tokenLifetime, // expiration
                '/api/authentication', // path
                null, // domain, null means that Symfony will generate it on its own.
                true, // secure
                true, // httpOnly
                false, // raw
                'strict' // same-site parameter, can be 'lax' or 'strict'.
            )
        );
        $event->getResponse()->headers->setCookie(
            new Cookie(
                'BEARER',
                $event->getData()['token'],
                time() + $this->tokenLifetime, // expiration
                '/', // path
                null, // domain, null means that Symfony will generate it on its own.
                true, // secure
                false, // httpOnly
                false, // raw
                'lax' // same-site parameter, can be 'lax' or 'strict'.
            )
        );
    }
}
