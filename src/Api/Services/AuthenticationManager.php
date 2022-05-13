<?php

namespace App\Api\Services;

use App\DB\Entity\User\User;
use App\User\UserManager;
use App\Utils\RequestHelper;
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
  protected RequestHelper $request_helper;

  public function __construct(
    TokenStorageInterface $token_storage,
    JWTTokenManagerInterface $jwt_manager,
    UserManager $user_manager,
    RefreshTokenGeneratorInterface $refresh_token_generator,
    RefreshTokenManagerInterface $refresh_manager,
    RequestHelper $request_helper,
    int $refresh_token_ttl // bind in services.yaml
  ) {
    $this->token_storage = $token_storage;
    $this->jwt_manager = $jwt_manager;
    $this->user_manager = $user_manager;
    $this->refresh_manager = $refresh_manager;
    $this->refresh_token_generator = $refresh_token_generator;
    $this->refresh_token_ttl = $refresh_token_ttl;
    $this->request_helper = $request_helper;
  }

  public function getAuthenticatedUser(): ?User
  {
    $token = $this->token_storage->getToken();

    if (!$token) {
      $tokenAsString = $this->extractTokenFromRequest();
      if (!empty($tokenAsString)) {
        return $this->getUserFromAuthenticationToken($tokenAsString);
      }

      return null;
    }

    $token = $this->token_storage->getToken();

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

  protected function getUserFromAuthenticationToken(string $token): ?User
  {
    $payload = $this->user_manager->decodeToken($token);
    $idClaim = $this->jwt_manager->getUserIdClaim();
    if (!isset($payload[$idClaim]) || 'username' !== $idClaim) {
      return null;
    }

    return $this->user_manager->findUserByUsername($payload[$idClaim]);
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

  protected function extractTokenFromRequest(): ?string
  {
    $request = $this->request_helper->getCurrentRequest();

    if (!$request->headers->has('Authorization')) {
      return null;
    }

    $authorizationHeader = $request->headers->get('Authorization');

    $headerParts = explode(' ', $authorizationHeader);
    if (!(2 === count($headerParts) && 0 === strcasecmp($headerParts[0], 'Bearer'))) {
      return null;
    }

    return $headerParts[1];
  }
}
