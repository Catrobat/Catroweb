<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use App\DB\Entity\User\User;
use App\Security\Authentication\AccountSessionCoordinator;
use App\Security\Authentication\AuthenticationSuccessResponseProcessor;
use Gesdinet\JWTRefreshTokenBundle\Security\Http\Authenticator\Token\PostRefreshTokenAuthenticationToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final readonly class ApiRefreshTokenSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(
    private AuthenticationSuccessHandlerInterface $inner_handler,
    private AuthenticationSuccessResponseProcessor $response_processor,
    private AccountSessionCoordinator $account_sessions,
  ) {
  }

  #[\Override]
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
  {
    $user = $token->getUser();
    if (!$user instanceof User || !$token instanceof PostRefreshTokenAuthenticationToken) {
      throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('No refresh-session proof.');
    }
    $response = $this->account_sessions->withVerifiedRefresh(
      $user,
      $token->getRefreshToken(),
      fn () => $this->inner_handler->onAuthenticationSuccess($request, $token),
    );
    if (!$response instanceof Response) {
      return new Response();
    }

    return $this->response_processor->process($request, $response);
  }
}
