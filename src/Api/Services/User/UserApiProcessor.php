<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Security\TokenGenerator;
use App\User\UserManager;
use Exception;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

final class UserApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly UserManager $user_manager, private readonly TokenGenerator $token_generator)
  {
  }

  /**
   * @throws Exception
   */
  public function registerUser(RegisterRequest $request): User
  {
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
      // TODO: implement User:about field
    }
    if (!empty($request->getCurrentlyWorkingOn())) {
      // TODO: implement User: get currently working on field
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
      // TODO: implement User:about field
    }
    if (!is_null($request->getCurrentlyWorkingOn())) {
      // TODO: implement User: get currently working on field
    }

    $this->user_manager->updateUser($user, true);
  }
}
