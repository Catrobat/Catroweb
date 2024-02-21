<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    ResponseCacheManager $response_cache_manager,
    private readonly CookieService $cookie_service
  ) {
    parent::__construct($translator, $serializer, $response_cache_manager);
  }

  public function createBasicUserDataResponse(User $user, string $attributes = null): BasicUserDataResponse
  {
    if (empty($attributes)) {
      $attributes_list = ['id', 'username'];
    } elseif ('ALL' === $attributes) {
      $attributes_list = ['id', 'username', 'picture', 'about', 'currently_working_on', 'projects', 'followers', 'following', 'ranking_score'];
    } else {
      $attributes_list = explode(',', $attributes);
    }

    return new BasicUserDataResponse($this->createBasicUserDataArray($user, $attributes_list));
  }

  public function createExtendedUserDataResponse(User $user, string $attributes = null): ExtendedUserDataResponse
  {
    if (empty($attributes)) {
      $attributes_list = ['id', 'username', 'email'];
    } elseif ('ALL' === $attributes) {
      $attributes_list = ['id', 'username', 'email', 'picture', 'about', 'currently_working_on', 'projects', 'followers', 'following'];
    } else {
      $attributes_list = explode(',', $attributes);
    }

    $data = $this->createBasicUserDataArray($user, $attributes_list);
    if (in_array('email', $attributes_list, true)) {
      $data['email'] = $user->getEmail() ?? '';
    }

    return new ExtendedUserDataResponse($data);
  }

  private function createBasicUserDataArray(User $user, array $attributes_list): array
  {
    $data = [];
    if (in_array('id', $attributes_list, true)) {
      $data['id'] = $user->getId();
    }
    if (in_array('username', $attributes_list, true)) {
      $data['username'] = $user->getUsername();
    }
    if (in_array('picture', $attributes_list, true)) {
      $data['picture'] = $user->getAvatar();
    }
    if (in_array('about', $attributes_list, true)) {
      $data['about'] = $user->getAbout() ?? '';
    }
    if (in_array('currently_working_on', $attributes_list, true)) {
      $data['currently_working_on'] = $user->getCurrentlyWorkingOn() ?? '';
    }
    if (in_array('projects', $attributes_list, true)) {
      $data['projects'] = $user->getProjects()->count();
    }
    if (in_array('followers', $attributes_list, true)) {
      $data['followers'] = $user->getFollowers()->count();
    }
    if (in_array('following', $attributes_list, true)) {
      $data['following'] = $user->getFollowing()->count();
    }

    return $data;
  }

  public function createUsersDataResponse(array $users, string $attributes = null): array
  {
    $users_data_response = [];
    foreach ($users as $user) {
      $user_data = $this->createBasicUserDataResponse($user, $attributes);
      $users_data_response[] = $user_data;
    }

    return $users_data_response;
  }

  public function createUserRegisteredResponse(string $token, string $refresh_token): JWTResponse
  {
    return new JWTResponse(
      [
        'token' => $token,
        'refresh_token' => $refresh_token,
      ]
    );
  }

  public function addAuthenticationCookiesToHeader(string $token, string $refresh_token, array &$responseHeaders): void
  {
    $responseHeaders['Set-Cookie'] = [
      $this->cookie_service->createBearerTokenCookie($token),
      $this->cookie_service->createRefreshTokenCookie($refresh_token),
    ];
  }
}
