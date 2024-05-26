<?php

declare(strict_types=1);

namespace App\Security\Authentication\JwtRefresh;

use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use App\User\UserManager;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RefreshTokenService
{
  public function __construct(
    protected int $refreshTokenLifetime,
    protected RefreshTokenManagerInterface $refresh_manager,
    protected UserManager $user_manager,
    protected JWTTokenManagerInterface $jwt_manager,
    protected CookieService $cookie_service,
  ) {
  }

  public function createRefreshTokenForUsername(string $username): RefreshTokenInterface
  {
    $datetime = new \DateTime('now');
    $datetime->modify('+'.$this->refreshTokenLifetime.' seconds');

    $refreshToken = $this->refresh_manager->create();
    $refreshToken->setUsername($username);
    $refreshToken->setRefreshToken();
    $refreshToken->setValid($datetime);

    $this->refresh_manager->save($refreshToken);

    return $refreshToken;
  }

  public function invalidateRefreshTokenOfUser(string $username): void
  {
    $refreshToken = $this->refresh_manager->getLastFromUsername($username);
    if (null !== $refreshToken) {
      $this->refresh_manager->delete($refreshToken);
    }
  }

  public function refreshBearerCookie(ResponseEvent $event): void
  {
    $refresh_token = $this->getRefreshTokenFromEvent($event);
    if ($this->isValidRefreshToken($refresh_token)) {
      /** @var User|null $user */
      $user = $this->user_manager->findUserByUsername($refresh_token->getUsername());
      if (null !== $user) {
        $new_bearer = $this->jwt_manager->create($user);
        $event->getResponse()->headers->setCookie($this->cookie_service->createBearerTokenCookie($new_bearer));

        return;
      }

      $this->refresh_manager->delete($refresh_token);
    }

    $this->cookie_service->clearCookie('REFRESH_TOKEN');
  }

  protected function getRefreshTokenFromEvent(KernelEvent $event): ?RefreshTokenInterface
  {
    return $this->refresh_manager->get($this->getRefreshCookieValue($event));
  }

  protected function isValidRefreshToken(?RefreshTokenInterface $refresh_token): bool
  {
    return $refresh_token instanceof RefreshTokenInterface && $refresh_token->isValid() && !empty($refresh_token->getUsername());
  }

  public function isBearerCookieSet(): bool
  {
    return isset($_COOKIE['BEARER']);
  }

  public function isRefreshTokenCookieSet(KernelEvent $event): bool
  {
    return $event->getRequest()->cookies->has('REFRESH_TOKEN');
  }

  protected function getBearerCookieValue(KernelEvent $event): string
  {
    return strval($event->getRequest()->cookies->get('BEARER'));
  }

  protected function getRefreshCookieValue(KernelEvent $event): string
  {
    return strval($event->getRequest()->cookies->get('REFRESH_TOKEN'));
  }

  protected function setAuthorizationHeader(KernelEvent $event, string $bearer_token): void
  {
    $event->getRequest()->headers->set('Authorization', 'Bearer '.$bearer_token);
  }
}
