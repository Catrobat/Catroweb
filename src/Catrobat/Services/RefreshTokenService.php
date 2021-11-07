<?php

namespace App\Catrobat\Services;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class RefreshTokenService
{
  private int $refreshTokenLifetime;
  private RefreshTokenManagerInterface $refresh_manager;

  public function __construct(int $refreshTokenLifetime, RefreshTokenManagerInterface $refresh_manager)
  {
    $this->refreshTokenLifetime = $refreshTokenLifetime;
    $this->refresh_manager = $refresh_manager;
  }

  /**
   * Create refresh token for user.
   */
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

  /**
   * Invalidate refresh token of user.
   */
  public function invalidateRefreshTokenOfUser(string $username): void
  {
    $refreshToken = $this->refresh_manager->getLastFromUsername($username);
    if (null !== $refreshToken) {
      $this->refresh_manager->delete($refreshToken);
    }
  }
}
