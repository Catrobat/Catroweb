<?php

namespace App\Catrobat\EventListener;

use App\Entity\User;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class ResettingSendEmailInitializeSubscriber implements EventSubscriberInterface
{
  private RouterInterface $router;

  private ContainerInterface $container;

  public function __construct(RouterInterface $router, ContainerInterface $container)
  {
    $this->router = $router;
    $this->container = $container;
  }

  public function onResettingSendEmailInitialize(?GetResponseNullableUserEvent $event): ?GetResponseNullableUserEvent
  {
    /** @var User|null $user */
    $user = $event->getUser();

    if (null === $user)
    {
      $url = $this->router->generate('reset_invalid');

      $event->setResponse(new RedirectResponse($url));
    }
    elseif ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl')))
    {
      $url = $this->router->generate('reset_already_requested');

      $event->setResponse(new RedirectResponse($url));
    }

    return $event;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onResettingSendEmailInitialize',
    ];
  }
}
