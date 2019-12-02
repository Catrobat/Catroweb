<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Requests\AppRequest;
use Doctrine\ORM\Query\Parameter;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class FlavorListener
 * @package App\Catrobat\Listeners
 */
class FlavorListener
{
  /**
   * @var ParameterBag
   */
  private $parameter_bag;

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
   * @param ParameterBagInterface $parameter_bag
   * @param RouterInterface $router
   * @param ActiveTheme $theme
   * @param AppRequest $app_request
   */
  public function __construct(ParameterBagInterface $parameter_bag, RouterInterface $router, ActiveTheme $theme,
                              AppRequest $app_request)
  {
    $this->parameter_bag = $parameter_bag;
    $this->router = $router;
    $this->theme = $theme;
    $this->app_request = $app_request;
  }

  /**
   * @param GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event)
  {
    // check the url for an requested flavor (needed to keep old flavoring alive)
    $current_url = $event->getRequest()->getUri();
    preg_match('>http(s)?://(.*?)/(.*)>', $current_url, $parsed_url);
    $parsed_url = explode("/", $parsed_url[3]);
    $url_requested_flavor = $parsed_url[0];

    if ((strpos($url_requested_flavor, '.php') !== false))
    {
      // skip index(.*?).php in url
      $url_requested_flavor = $parsed_url[1];
    }

    $context = $this->router->getContext();
    $attributes = $event->getRequest()->attributes;
    $session = $event->getRequest()->getSession();

    if ($this->checkFlavor($url_requested_flavor))
    {
      $this->theme->setName($url_requested_flavor);
      $attributes->set('flavor', $url_requested_flavor);
      $context->setParameter('flavor', $url_requested_flavor);
      $session->set('flavor', $url_requested_flavor);
      $session->set('flavor_context', $url_requested_flavor);
    }

    else
    {
      if ($url_requested_flavor == 'app')
      {
        $requested_theme = $this->app_request->getThemeDefinedInRequest();
        if ($requested_theme !== "" && $this->checkFlavor($requested_theme))
        {
          $attributes->set('flavor', $requested_theme);
          $this->theme->setName($requested_theme);
          $session->set('flavor', $requested_theme);
        }
        else
        {
          // no specific theme was requested, use the default one
          $attributes->set('flavor', 'pocketcode');
          $this->theme->setName('pocketcode');
          $session->set('flavor', 'pocketcode');
        }
        $context->setParameter('flavor', 'app');
        $session->set('flavor_context', 'app');
      }

      else
      {
        if ($session->has('flavor'))
        {
          $this->theme->setName($session->get('flavor'));
          $context->setParameter('flavor', $session->get('flavor_context'));
        }
        else
        {
          $attributes->set('flavor', 'pocketcode');
          $this->theme->setName('pocketcode');
          $context->setParameter('flavor', 'app');
          $session->set('flavor_context', 'app');
        }
      }
    }
  }

  public function checkFlavor($flavor): bool
  {
    $flavor_options = $this->parameter_bag->get('themes');

    return in_array($flavor, $flavor_options);
  }

}
