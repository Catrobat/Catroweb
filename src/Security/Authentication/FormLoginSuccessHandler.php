<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\DB\Entity\User\User;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
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
    private readonly AccountSessionCoordinator $account_sessions,
    private readonly TokenStorageInterface $token_storage,
  ) {
    parent::__construct($http_utils);
  }

  #[\Override]
  public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
  {
    try {
      $response = parent::onAuthenticationSuccess($request, $token);

      if (!$response instanceof Response) {
        return null;
      }

      $user = $token->getUser();
      if (!$user instanceof User) {
        throw new BadCredentialsException('No authenticated account.');
      }

      return $this->account_sessions->withVerifiedLogin($user, function () use ($user, $response): Response {
        $refresh_token = $this->refresh_token_service->createRefreshTokenForUsername($user->getUserIdentifier());
        $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refresh_token->getRefreshToken()));
        $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));

        return $response;
      });
    } catch (\Throwable $exception) {
      // AuthenticatorManager installed this token before invoking the success handler.
      // A locked user reload can update its hash even though issuance is then rejected.
      $this->token_storage->setToken(null);
      if ($request->hasSession()) {
        $request->getSession()->remove('_security_main');
      }

      throw $exception;
    }
  }
}
