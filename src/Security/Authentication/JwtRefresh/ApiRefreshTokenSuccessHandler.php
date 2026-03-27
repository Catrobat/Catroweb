<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use App\Security\Authentication\AuthenticationSuccessResponseProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class ApiRefreshTokenSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(
    private AuthenticationSuccessHandlerInterface $inner_handler,
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

    return $this->response_processor->process($request, $response);
  }
}
