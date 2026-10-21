<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class ApiAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(
    private AuthenticationSuccessHandlerInterface $inner_handler,
    private AuthenticationModeResolver $mode_resolver,
    private MainFirewallSessionLogin $main_firewall_session_login,
    private AuthenticationSuccessResponseProcessor $response_processor,
  ) {
  }

  #[\Override]
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
  {
    $response = $this->inner_handler->onAuthenticationSuccess($request, $token);
    if (!$response instanceof Response) {
      return new Response();
    }

    if ($this->mode_resolver->isCookieMode($request)) {
      $this->main_firewall_session_login->login($request, $token);
    }

    return $this->response_processor->process($request, $response);
  }
}
