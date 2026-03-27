<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use App\Security\Authentication\AuthenticationModeResolver;
use App\Security\Authentication\AuthenticationSuccessResponseProcessor;
use App\Security\Authentication\CookieService;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

trait AuthenticationTestFactory
{
  private function createResponseProcessor(): AuthenticationSuccessResponseProcessor
  {
    return new AuthenticationSuccessResponseProcessor(
      new AuthenticationModeResolver(),
      $this->createCookieService(),
    );
  }

  private function createCookieService(): CookieService
  {
    $router = $this->createStub(RouterInterface::class);
    $router->method('getContext')->willReturn(new RequestContext('/base'));

    return new CookieService(3600, 7200, $router);
  }
}
