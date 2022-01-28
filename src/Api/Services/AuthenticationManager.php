<?php

namespace App\Api\Services;

use App\Entity\User;
use App\Manager\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationManager
{
  private TokenStorageInterface $token_storage;
  private JWTTokenManagerInterface $jwt_manager;
  private UserManager $user_manager;

  public function __construct(TokenStorageInterface $token_storage, JWTTokenManagerInterface $jwt_manager, UserManager $user_manager)
  {
    $this->token_storage = $token_storage;
    $this->jwt_manager = $jwt_manager;
    $this->user_manager = $user_manager;
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
}
