<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Requests\AppRequest;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;


/**
 * Class FlavorListener
 * @package App\Catrobat\Listeners
 */
class FlavorListener
{
  /**
   * @var RouterInterface
   */
  private $router;

  /**
   * @var ActiveTheme
   */
  private $theme;

  /**
   * @var AppRequest
   */
  private $app_request;

  /**
   * FlavorListener constructor.
   *
   * @param RouterInterface $router
   * @param $theme
   * @param AppRequest $app_request
   */
  public function __construct(RouterInterface $router, $theme, AppRequest $app_request)
  {
    $this->router = $router;
    $this->theme = $theme;
    $this->app_request = $app_request;
  }

  /**
   * @param GetResponseEvent $event
   */
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
      $context->setParameter('flavor', "app");
    }

    if ($attributes->get('flavor') === 'app') {

      $requested_theme = $this->app_request->getThemeDefinedInRequest();

      if ($requested_theme !== "")
      {
        $event->getRequest()->attributes->set('flavor', $requested_theme);
        $this->theme->setName($requested_theme);
      }
      else
      {
        // no specific theme was requested, use the default one
        $event->getRequest()->attributes->set('flavor', 'pocketcode');
        $this->theme->setName('pocketcode');
      }
    }
    else
    {
      $this->theme->setName($attributes->get('flavor'));
    }
  }
}
