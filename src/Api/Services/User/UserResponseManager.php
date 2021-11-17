<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractResponseManager;
use App\Entity\User;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;

final class UserResponseManager extends AbstractResponseManager
{
  public function createBasicUserDataResponse(User $user): BasicUserDataResponse
  {
    return new BasicUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ]);
  }

  public function createExtendedUserDataResponse(User $user): ExtendedUserDataResponse
  {
    return new ExtendedUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'email' => $user->getEmail(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ]);
  }

  public function createUsersDataResponse(array $users): array
  {
    $users_data_response = [];
    foreach ($users as $user) {
      $user_data = $this->createBasicUserDataResponse($user);
      $users_data_response[] = $user_data;
    }

    return $users_data_response;
  }

  public function createUserRegisteredResponse(string $token): JWTResponse
  {
    return new JWTResponse(
      [
        'token' => $token,
        'refresh_token' => 'ToDo!',
      ]
    );
  }
}
