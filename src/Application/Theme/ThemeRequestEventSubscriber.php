<?php

declare(strict_types=1);

namespace App\Application\Theme;

use App\DB\Entity\Flavor;
use App\Utils\RequestHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class ThemeRequestEventSubscriber implements EventSubscriberInterface
{
  private string $routing_theme;

  private string $flavor;

  public function __construct(
    private readonly ParameterBagInterface $parameter_bag,
    private readonly RouterInterface $router,
    private readonly RequestHelper $app_request
  ) {
    $this->routing_theme = (string) $parameter_bag->get('umbrellaTheme');
    $this->flavor = (string) $parameter_bag->get('defaultFlavor');
  }

  public function onKernelRequest(RequestEvent $event): void
  {
    if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
      // In sub-request we can just re-use the theme from the master request
      $this->setupRouting();
      $this->setupRequestAttributes($event->getRequest());

      return;
    }

    // Themes must be defined in a request, not in the URI!
    $requested_theme = $this->app_request->getThemeDefinedInRequest();

    // URI should not contain a flavor but the umbrella theme
    $this->routing_theme = (string) $this->parameter_bag->get('umbrellaTheme');

    if ('' === $requested_theme) { // - @deprecated
      // However, we still support legacy theming
      $requested_theme = $this->getThemeFromUrl($event);

      // Here we have to keep the flavoring as routing theme
      if ($this->parameter_bag->get('adminTheme') !== $requested_theme) {
        $this->routing_theme = $requested_theme;
      }
    }

    $this->flavor = $this->getFlavorFromTheme($requested_theme);

    $this->setupRouting();
    $this->setupRequestAttributes($event->getRequest());
  }

  private function setupRouting(): void
  {
    $this->router->getContext()->setParameter('theme', $this->routing_theme);
  }

  private function setupRequestAttributes(Request $request): void
  {
    $request->attributes->set('theme', $this->routing_theme);
    $request->attributes->set('flavor', $this->flavor);
  }

  private function getThemeFromUrl(RequestEvent $event): string
  {
    $current_url = $event->getRequest()->getUri();
    preg_match('#http(s)?://(.*?)(/.*?\.php)?/(.*)#', $current_url, $parsed_url);

    return explode('/', $parsed_url[4])[0];
  }

  private function getFlavorFromTheme(string $theme): string
  {
    return $this->flavorExists($theme) ? $theme : Flavor::POCKETCODE;
  }

  private function flavorExists(?string $flavor): bool
  {
    $flavors = $this->parameter_bag->get('flavors');

    return in_array($flavor, $flavors, true);
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::REQUEST => ['onKernelRequest', 10]];
  }
}
