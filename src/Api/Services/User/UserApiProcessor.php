<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use Exception;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

final class UserApiProcessor extends AbstractApiProcessor
{
  private UserManager $user_manager;

  private TokenGenerator $token_generator;

  public function __construct(UserManager $user_manager, TokenGenerator $token_generator)
  {
    $this->token_generator = $token_generator;
    $this->user_manager = $user_manager;
  }

  /**
   * @throws Exception
   */
  public function registerUser(RegisterRequest $request): User
  {
    /** @var User $user */
    $user = $this->user_manager->createUser();
    $user->setUsername($request->getUsername());
    $user->setEmail($request->getEmail());
    $user->setPlainPassword($request->getPassword());
    $user->setEnabled(true);
    $user->setVerified(false);
    $user->setUploadToken($this->token_generator->generateToken());
    $this->user_manager->updateUser($user);

    return $user;
  }

  public function deleteUser(User $user): void
  {
    $this->user_manager->delete($user);
  }

  public function updateUser(User $user, UpdateUserRequest $request): void
  {
    if (!empty($request->getEmail())) {
      $user->setEmail($request->getEmail());
    }
    if (!empty($request->getUsername())) {
      $user->setUsername($request->getUsername());
    }
    if (!empty($request->getPassword())) {
      $user->setPassword($request->getPassword());
    }

    $this->user_manager->updateUser($user, true);
  }
}
