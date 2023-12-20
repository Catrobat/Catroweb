<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\User\UserApiFacade;
use App\User\ResetPassword\PasswordResetRequestedEvent;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;

class UserApi extends AbstractApiController implements UserApiInterface
{
  public function __construct(private readonly UserApiFacade $facade) {}

  /**
   * @throws \Exception
   */
  public function userPost(RegisterRequest $register_request, string $accept_language, int &$responseCode, array &$responseHeaders): null|JWTResponse|RegisterErrorResponse
  {
    $validation_wrapper = $this->facade->getRequestValidator()->validateRegistration($register_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new RegisterErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    if ($register_request->isDryRun()) {
      $responseCode = Response::HTTP_NO_CONTENT;

      return null;
    }

    $user = $this->facade->getProcessor()->registerUser($register_request);

    $responseCode = Response::HTTP_CREATED;
    $token = $this->facade->getAuthenticationManager()->createAuthenticationTokenFromUser($user);
    $refresh_token = $this->facade->getAuthenticationManager()->createRefreshTokenByUser($user);
    $response = $this->facade->getResponseManager()->createUserRegisteredResponse($token, $refresh_token);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function userDelete(int &$responseCode, array &$responseHeaders): void
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    $this->facade->getProcessor()->deleteUser($this->facade->getAuthenticationManager()->getAuthenticatedUser());
  }

  public function userGet(&$responseCode, array &$responseHeaders): ExtendedUserDataResponse
  {
    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createExtendedUserDataResponse(
      $this->facade->getAuthenticationManager()->getAuthenticatedUser(), null
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function userIdGet(string $id, int &$responseCode, array &$responseHeaders): ?BasicUserDataResponse
  {
    $user = $this->facade->getLoader()->findUserByID($id);

    if (null === $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createBasicUserDataResponse($user, 'ALL');
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function userPut(UpdateUserRequest $update_user_request, string $accept_language, int &$responseCode, array &$responseHeaders): null|array|object
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($user, $update_user_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new UpdateUserErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $responseCode = Response::HTTP_NO_CONTENT;

    if (!$update_user_request->isDryRun()) {
      $this->facade->getProcessor()->updateUser(
        $user, $update_user_request
      );

      if (!is_null($update_user_request->getUsername())) {
        $token = $this->facade->getAuthenticationManager()->createAuthenticationTokenFromUser($user);
        $refresh_token = $this->facade->getAuthenticationManager()->createRefreshTokenByUser($user);
        $this->facade->getResponseManager()->addAuthenticationCookiesToHeader($token, $refresh_token, $responseHeaders);
      }
    }

    return null;
  }

  public function usersSearchGet(string $query, int $limit, int $offset, string $attributes, int &$responseCode, array &$responseHeaders): array
  {
    $users = $this->facade->getLoader()->searchUsers($query, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersDataResponse($users, $attributes);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function userResetPasswordPost(ResetPasswordRequest $reset_password_request, string $accept_language, int &$responseCode, array &$responseHeaders): ?RegisterErrorResponse
  {
    $validation_wrapper = $this->facade->getRequestValidator()->validateResetPasswordRequest($reset_password_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new RegisterErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    // Do not reveal whether a user account was found or not.
    $this->facade->getEventDispatcher()->dispatch(new PasswordResetRequestedEvent($reset_password_request->getEmail(), $accept_language));
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }

  public function usersGet(string $query, int $limit, int $offset, int &$responseCode, array &$responseHeaders): null|array|object
  {
      $users = $this->facade->getLoader()->getAllUsers($query, $limit, $offset);

      $responseCode = Response::HTTP_OK;
      $response = $this->facade->getResponseManager()->createUsersDataResponse($users, 'ALL');
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $response;
  }
}
