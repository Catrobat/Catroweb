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
  public function __construct(private readonly TokenStorageInterface $token_storage, private readonly JWTTokenManagerInterface $jwt_manager, private readonly UserManager $user_manager, private readonly RefreshTokenGeneratorInterface $refresh_token_generator, private readonly RefreshTokenManagerInterface $refresh_manager, protected RequestHelper $request_helper, protected int $refresh_token_ttl)
  {
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

    $user = $this->user_manager->findUserByUsername($payload[$idClaim]);
    if (null === $user || $user instanceof User) {
      return $user;
    }

    throw new \Exception("Can't get user from auth token");
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
      ->getRefreshToken() ?? ''
    ;
  }

  protected function extractTokenFromRequest(): ?string
  {
    $request = $this->request_helper->getCurrentRequest();

    if (!$request->headers->has('Authorization')) {
      return null;
    }

    $authorizationHeader = $request->headers->get('Authorization');

    $headerParts = explode(' ', (string) $authorizationHeader);
    if (!(2 === count($headerParts) && 0 === strcasecmp($headerParts[0], 'Bearer'))) {
      return null;
    }

    return $headerParts[1];
  }
}
