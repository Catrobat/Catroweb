<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class OAuthSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  public function __construct(
    private CookieService $cookie_service,
    private JWTTokenManagerInterface $jwt_manager,
    private RefreshTokenService $refresh_token_service,
    private UrlGeneratorInterface $url_generator,
  ) {
  }

  #[\Override]
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
  {
    $user = $token->getUser();
    $refreshToken = $this->refresh_token_service->createRefreshTokenForUsername($user->getUserIdentifier());

    $redirectUrl = ($user instanceof User && null === $user->getDateOfBirth())
      ? $this->url_generator->generate('complete_registration')
      : '/';
    $response = new RedirectResponse($redirectUrl);
    $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refreshToken->getRefreshToken()));
    $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));

    return $response;
  }
}
