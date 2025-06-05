<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\User\UserManager;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

class UserApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly UserManager $user_manager)
  {
  }

  /**
   * @throws \Exception
   */
  public function registerUser(RegisterRequest $request): User
  {
    $user = $this->user_manager->create();
    $user->setUsername($request->getUsername());
    $user->setEmail($request->getEmail());
    $user->setPlainPassword($request->getPassword());
    $user->setEnabled(true);
    $user->setVerified(false);

    $this->user_manager->updateUser($user);

    if (null !== $request->getPicture() && '' !== $request->getPicture() && '0' !== $request->getPicture()) {
      // Resize happens in UserRequestValidator::validateAndResizePicture
      $user->setAvatar($request->getPicture());
    }

    if (null !== $request->getAbout() && '' !== $request->getAbout() && '0' !== $request->getAbout()) {
      $user->setAbout($request->getAbout());
    }

    if (null !== $request->getCurrentlyWorkingOn() && '' !== $request->getCurrentlyWorkingOn() && '0' !== $request->getCurrentlyWorkingOn()) {
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
    if (null !== $request->getEmail() && '' !== $request->getEmail() && '0' !== $request->getEmail()) {
      $user->setEmail($request->getEmail());
    }

    if (null !== $request->getUsername() && '' !== $request->getUsername() && '0' !== $request->getUsername()) {
      $user->setUsername($request->getUsername());
    }

    if (null !== $request->getPassword() && '' !== $request->getPassword() && '0' !== $request->getPassword()) {
      $user->setPlainPassword($request->getPassword());
    }

    if (!is_null($request->getPicture())) {
      if ('' === $request->getPicture() || '0' === $request->getPicture()) {
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

    $this->user_manager->updateUser($user);
  }
}
