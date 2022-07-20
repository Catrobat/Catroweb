<?php

namespace App\Security\OAuth;

use App\Security\Authentication\CookieService;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class OAuthSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(
    private readonly CookieService $cookie_service,
    private readonly JWTTokenManagerInterface $jwt_manager,
    private readonly RefreshTokenService $refresh_token_service
  ) {
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
  {
    $user = $token->getUser();
    $refreshToken = $this->refresh_token_service->createRefreshTokenForUsername($user->getUserIdentifier());
    $response = new RedirectResponse('/');
    $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refreshToken->getRefreshToken()));
    $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));

    return $response;
  }
}
