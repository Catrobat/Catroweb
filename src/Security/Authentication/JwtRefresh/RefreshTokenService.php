<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use App\User\UserManager;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RefreshTokenService
{
  public const string REFRESHED_BEARER_COOKIE_ATTRIBUTE = '_refreshed_bearer_cookie';
  public const string CLEAR_AUTHENTICATION_COOKIES_ATTRIBUTE = '_clear_authentication_cookies';

  public function __construct(
    #[Autowire('%env(REFRESH_TOKEN_TTL)%')]
    protected int $refreshTokenLifetime,
    protected RefreshTokenManagerInterface $refresh_manager,
    protected RefreshTokenGeneratorInterface $refresh_token_generator,
    protected UserManager $user_manager,
    protected JWTTokenManagerInterface $jwt_manager,
    protected CookieService $cookie_service,
  ) {
  }

  /**
   * @throws \DateMalformedStringException
   */
  public function createRefreshTokenForUsername(string $username): RefreshTokenInterface
  {
    $user = $this->user_manager->findUserByUsername($username);
    if (!$user instanceof User) {
      throw new \InvalidArgumentException(\sprintf('User "%s" not found.', $username));
    }

    return $this->refresh_token_generator->createForUserWithTtl($user, $this->refreshTokenLifetime);
  }

  public function invalidateRefreshTokenOfUser(string $username): void
  {
    $refreshToken = $this->refresh_manager->getLastFromUsername($username);
    if (null !== $refreshToken) {
      $this->refresh_manager->delete($refreshToken);
    }
  }

  public function refreshRequestAuthentication(Request $request): void
  {
    if ($request->headers->has('Authorization') || !$request->cookies->has('REFRESH_TOKEN')) {
      return;
    }

    if ($this->hasValidBearerCookie($request)) {
      return;
    }

    $refresh_token = $this->getRefreshTokenFromRequest($request);
    if ($this->isValidRefreshToken($refresh_token)) {
      /** @var User|null $user */
      $user = $this->user_manager->findUserByUsername($refresh_token->getUsername());
      if (null !== $user) {
        $new_bearer = $this->jwt_manager->create($user);
        $request->attributes->set(self::REFRESHED_BEARER_COOKIE_ATTRIBUTE, $new_bearer);
        // Update both Symfony's ParameterBag and the PHP superglobal so that
        // downstream code works regardless of which source it reads from.
        $request->cookies->set('BEARER', $new_bearer);
        $_COOKIE['BEARER'] = $new_bearer;
        $this->setAuthorizationHeader($request, $new_bearer);

        return;
      }

      $this->refresh_manager->delete($refresh_token);
    }

    $this->markAuthenticationCookiesForClearing($request);
  }

  public function syncAuthenticationCookies(ResponseEvent $event): void
  {
    $request = $event->getRequest();
    $refreshed_bearer = $request->attributes->get(self::REFRESHED_BEARER_COOKIE_ATTRIBUTE);
    if (\is_string($refreshed_bearer) && '' !== $refreshed_bearer) {
      $event->getResponse()->headers->setCookie($this->cookie_service->createBearerTokenCookie($refreshed_bearer));
    }

    if (true === $request->attributes->get(self::CLEAR_AUTHENTICATION_COOKIES_ATTRIBUTE)) {
      $event->getResponse()->headers->setCookie($this->cookie_service->createClearedCookie('BEARER'));
      $event->getResponse()->headers->setCookie($this->cookie_service->createClearedCookie('REFRESH_TOKEN'));
    }
  }

  protected function isValidRefreshToken(?RefreshTokenInterface $refresh_token): bool
  {
    return $refresh_token instanceof RefreshTokenInterface && $refresh_token->isValid() && !empty($refresh_token->getUsername());
  }

  protected function getRefreshTokenFromRequest(Request $request): ?RefreshTokenInterface
  {
    return $this->refresh_manager->get($this->getRefreshCookieValue($request));
  }

  protected function getBearerCookieValue(Request $request): string
  {
    return strval($request->cookies->get('BEARER'));
  }

  protected function getRefreshCookieValue(Request $request): string
  {
    return strval($request->cookies->get('REFRESH_TOKEN'));
  }

  protected function hasValidBearerCookie(Request $request): bool
  {
    $bearer_token = $this->getBearerCookieValue($request);
    if ('' === $bearer_token) {
      return false;
    }

    try {
      $this->jwt_manager->parse($bearer_token);

      return true;
    } catch (JWTDecodeFailureException) {
      return false;
    }
  }

  protected function setAuthorizationHeader(Request $request, string $bearer_token): void
  {
    $request->headers->set('Authorization', 'Bearer '.$bearer_token);
  }

  protected function markAuthenticationCookiesForClearing(Request $request): void
  {
    $request->attributes->set(self::CLEAR_AUTHENTICATION_COOKIES_ATTRIBUTE, true);
    $request->cookies->remove('BEARER');
    $request->cookies->remove('REFRESH_TOKEN');
    unset($_COOKIE['BEARER'], $_COOKIE['REFRESH_TOKEN']);
  }
}
