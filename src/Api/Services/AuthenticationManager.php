<?php

namespace App\Api\Services;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationManager
{
  private TokenStorageInterface $token_storage;
  private JWTTokenManagerInterface $jwt_manager;
  private UserManager $user_manager;
  private RefreshTokenManagerInterface $refresh_manager;
  private RefreshTokenGeneratorInterface $refresh_token_generator;
  protected int $refresh_token_ttl;

  public function __construct(
    TokenStorageInterface $token_storage,
    JWTTokenManagerInterface $jwt_manager,
    UserManager $user_manager,
    RefreshTokenGeneratorInterface $refresh_token_generator,
    RefreshTokenManagerInterface $refresh_manager,
    int $refresh_token_ttl // bind in services.yml
  ) {
    $this->token_storage = $token_storage;
    $this->jwt_manager = $jwt_manager;
    $this->user_manager = $user_manager;
    $this->refresh_manager = $refresh_manager;
    $this->refresh_token_generator = $refresh_token_generator;
    $this->refresh_token_ttl = $refresh_token_ttl;
  }

  public function getAuthenticatedUser(): ?User
  {
    $token = $this->token_storage->getToken();
    if (!$token) {
      return null;
    }

    $user = $token->getUser();
    if (!($user instanceof User)) {
      $user = null;
    }

    return $user;
  }

  public function createAuthenticationTokenFromUser(User $user): string
  {
    return $this->jwt_manager->create($user);
  }

  public function getUserFromAuthenticationToken(string $token): ?User
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1], true);

    $jwtPayload = json_decode($tokenPayload, true);

    if (!array_key_exists('username', $jwtPayload)) {
      return null;
    }

    return $this->user_manager->findUserByUsername($jwtPayload['username']);
  }

  public function deleteRefreshToken(string $x_refresh): bool
  {
    $refreshToken = $this->refresh_manager->get($x_refresh);
    if (null === $refreshToken) {
      return false;
    }
    $this->refresh_manager->delete($refreshToken);

    return true;
  }

  public function createRefreshTokenByUser(User $user): string
  {
    return $this->refresh_token_generator
      ->createForUserWithTtl($user, $this->refresh_token_ttl)
      ->getRefreshToken() ?? '';
  }
}
