<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Moderation\LinkDetector;
use App\Security\PasswordGenerator;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly AuthenticationManager $authentication_manager,
    protected AchievementManager $achievement_manager,
    private readonly LinkDetector $link_detector = new LinkDetector(),
  ) {
  }

  public function createJWTByUser(User $user): string
  {
    return $this->authentication_manager->createAuthenticationTokenFromUser($user);
  }

  public function createRefreshTokenByUser(User $user): string
  {
    return $this->authentication_manager->createRefreshTokenByUser($user);
  }

  /**
   * @param array<string, mixed> $payload claims already verified by AuthenticationRequestValidator::verify()
   *
   * @throws \Exception
   */
  public function connectUserToAccount(array $payload, string $resource_owner): User
  {
    $user_id = $payload['id'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';

    // A missing id would turn the lookup below into `<owner>_id IS NULL` and match an arbitrary account.
    if (!is_string($user_id) || '' === $user_id || !is_string($email) || '' === $email) {
      throw new \RuntimeException(sprintf('The %s id token has no user id or email', $resource_owner));
    }

    /** @var User|null $user */
    $user = $this->user_manager->findOneBy([$resource_owner.'_id' => $user_id]);
    if ($user) {
      // User already exists and is already connected to this service
      return $user;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($email);
    if ($user) {
      // User already exists but is not connected to this service
      try {
        $this->user_manager->claimUnverifiedAccountForOAuth($user, filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN));
      } catch (CustomUserMessageAccountStatusException $e) {
        throw new HttpException(Response::HTTP_UNAUTHORIZED, $e->getMessageKey(), $e);
      }
      $this->setProviderId($user, $resource_owner, $user_id);
      $this->user_manager->updateUser($user);

      return $user;
    }

    // User does not exist yet
    $user = $this->user_manager->create();
    $this->setProviderId($user, $resource_owner, $user_id);
    $user->setEnabled(true);
    $user->setEmail($email);
    $user->setUsername($this->createRandomUsername($name));
    $user->setPassword(PasswordGenerator::generateRandomPassword());
    $user->setOauthUser(true);
    $user->setVerified(true);
    $this->achievement_manager->unlockAchievementAccountVerification($user);

    $this->user_manager->updateUser($user);

    return $user;
  }

  private function setProviderId(User $user, string $resource_owner, string $id): void
  {
    match ($resource_owner) {
      'google' => $user->setGoogleId($id),
      'facebook' => $user->setFacebookId($id),
      'apple' => $user->setAppleId($id),
      default => throw new \LogicException(sprintf('Unknown OAuth provider "%s"', $resource_owner)),
    };
  }

  public function deleteRefreshToken(string $x_refresh): bool
  {
    return $this->authentication_manager->deleteRefreshToken($x_refresh);
  }

  protected function createRandomUsername(?string $name = null): string
  {
    $username_base = 'user';
    // The provider's display name is user-chosen, so it can carry an advertising link
    if (null !== $name && '' !== $name && '0' !== $name && !$this->link_detector->containsLink($name)) {
      $username_base = str_replace(' ', '', $name);
    }

    $username = $username_base;
    $user_number = 0;
    while ($this->user_manager->findUserByUsername($username) instanceof UserInterface) {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }
}
