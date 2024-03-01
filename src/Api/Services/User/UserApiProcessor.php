<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Security\TokenGenerator;
use App\User\UserManager;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

class UserApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly UserManager $user_manager, private readonly TokenGenerator $token_generator)
  {
  }

  /**
   * @throws \Exception
   */
  public function registerUser(RegisterRequest $request): User
  {
    /** @var User $user */
    $user = $this->user_manager->create();
    $user->setUsername($request->getUsername());
    $user->setEmail($request->getEmail());
    $user->setPlainPassword($request->getPassword());
    $user->setEnabled(true);
    $user->setVerified(false);
    $user->setUploadToken($this->token_generator->generateToken());
    $this->user_manager->updateUser($user);

    if (!empty($request->getPicture())) {
      // Resize happens in UserRequestValidator::validateAndResizePicture
      $user->setAvatar($request->getPicture());
    }
    if (!empty($request->getAbout())) {
      $user->setAbout($request->getAbout());
    }
    if (!empty($request->getCurrentlyWorkingOn())) {
      $user->setCurrentlyWorkingOn($request->getCurrentlyWorkingOn());
    }

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
      $user->setPlainPassword($request->getPassword());
    }
    if (!is_null($request->getPicture())) {
      if (empty($request->getPicture())) {
        $user->setAvatar(null);
      } else {
        // Resize happens in UserRequestValidator::validateAndResizePicture
        $user->setAvatar($request->getPicture());
      }
    }
    if (!is_null($request->getAbout())) {
      $user->setAbout($request->getAbout());
    }
    if (!is_null($request->getCurrentlyWorkingOn())) {
      $user->setCurrentlyWorkingOn($request->getCurrentlyWorkingOn());
    }

    $this->user_manager->updateUser($user, true);
  }
}
