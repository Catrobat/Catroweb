<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\User\UserApiFacade;
use App\Event\PasswordResetRequestedEvent;
use Exception;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;

final class UserApi extends AbstractApiController implements UserApiInterface
{
  private UserApiFacade $facade;

  public function __construct(UserApiFacade $facade)
  {
    $this->facade = $facade;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function userPost(RegisterRequest $register_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);

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
    $response = $this->facade->getResponseManager()->createUserRegisteredResponse($token);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    $this->facade->getProcessor()->deleteUser($this->facade->getAuthenticationManager()->getAuthenticatedUser());

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function userGet(&$responseCode, array &$responseHeaders): ExtendedUserDataResponse
  {
    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createExtendedUserDataResponse(
      $this->facade->getAuthenticationManager()->getAuthenticatedUser()
    );
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $user = $this->facade->getLoader()->findUserByID($id);

    if (null === $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createBasicUserDataResponse($user);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);

    $validation_wrapper = $this->facade->getRequestValidator()->validateUpdateRequest($update_user_request, $accept_language);

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
        $this->facade->getAuthenticationManager()->getAuthenticatedUser(), $update_user_request
      );
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function usersSearchGet(string $query, ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null): array
  {
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);

    $users = $this->facade->getLoader()->searchUsers($query, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->facade->getResponseManager()->createUsersDataResponse($users);
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userResetPasswordPost(ResetPasswordRequest $reset_password_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $validation_wrapper = $this->facade->getRequestValidator()->validateResetPasswordRequest($reset_password_request, $accept_language);

    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error_response = new RegisterErrorResponse($validation_wrapper->getErrors());
      $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $error_response);
      $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

      return $error_response;
    }

    $this->facade->getEventDispatcher()->dispatch(new PasswordResetRequestedEvent($reset_password_request->getEmail(), $accept_language));
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }
}
