<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Extends the default form_login success handler to also set JWT cookies so
 * that the stateless API firewall can authenticate requests made by the web UI
 * immediately after a web-form login (Bearer token required for API calls).
 */
class FormLoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
  public function __construct(
    private readonly CookieService $cookie_service,
    private readonly JWTTokenManagerInterface $jwt_manager,
    private readonly RefreshTokenService $refresh_token_service,
    HttpUtils $http_utils,
  ) {
    parent::__construct($http_utils);
  }

  #[\Override]
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
  {
    $response = parent::onAuthenticationSuccess($request, $token);

    if (!$response instanceof Response) {
      return null;
    }

    $user = $token->getUser();
    $refresh_token = $this->refresh_token_service->createRefreshTokenForUsername($user->getUserIdentifier());
    $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refresh_token->getRefreshToken()));
    $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));

    return $response;
  }
}
