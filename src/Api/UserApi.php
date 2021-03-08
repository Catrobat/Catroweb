<?php

namespace App\Api;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\APIHelper;
use Exception;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserApi implements UserApiInterface
{
  private string $token;

  private ValidatorInterface $validator;

  private UserManager $user_manager;

  private TokenGenerator $token_generator;

  private TranslatorInterface $translator;

  private TokenStorageInterface $token_storage;

  private JWTTokenManagerInterface $jwt_manager;

  private RefreshTokenManagerInterface $refresh_token_manager;

  public function __construct(ValidatorInterface $validator, UserManager $user_manager,
                              TokenGenerator $token_generator, TranslatorInterface $translator,
                              TokenStorageInterface $token_storage, JWTTokenManagerInterface $jwt_manager,
                              RefreshTokenManagerInterface $refresh_token_manager)
  {
    $this->validator = $validator;
    $this->user_manager = $user_manager;
    $this->token_generator = $token_generator;
    $this->translator = $translator;
    $this->token_storage = $token_storage;
    $this->jwt_manager = $jwt_manager;
    $this->refresh_token_manager = $refresh_token_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = APIHelper::getPandaAuth($value);
  }

  /**
   * {@inheritdoc}
   */
  public function userPost(RegisterRequest $register_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    $validation_schema = $this->validateRegistration($register_request);

    if ($validation_schema->getEmail() || $validation_schema->getUsername() || $validation_schema->getPassword())
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => Unprocessable entity

      return $validation_schema;
    }

    if ($register_request->isDryRun())
    {
      $responseCode = Response::HTTP_NO_CONTENT; // 204 => Dry-run successful, no validation error

      return null;
    }

    // Validation successful, no dry-run requested => we can actually register the user
    /** @var User $user */
    $user = $this->user_manager->createUser();
    $user->setUsername($register_request->getUsername());
    $user->setEmail($register_request->getEmail());
    $user->setPlainPassword($register_request->getPassword());
    $user->setEnabled(true);
    $user->setUploadToken($this->token_generator->generateToken());
    $this->user_manager->updateUser($user);

    $token = $this->jwt_manager->create($user);
    $refresh = $this->refresh_token_manager->create();
    $refresh->setUsername($user->getUsername());

    if (null === $refresh->getUsername())
    {
      $refresh_token = 'fail';
    }
    else
    {
      $refresh_token = 'ok';
    }

    $responseCode = Response::HTTP_CREATED; // 201 => User successfully registered

    return new JWTResponse(
      [
        'token' => $token,
        'refresh_token' => $refresh_token,
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    $this->user_manager->delete($user);

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function userGet(&$responseCode, array &$responseHeaders): ExtendedUserDataResponse
  {
    $responseCode = Response::HTTP_OK;

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    return $this->getExtendedUserDataResponse($user);
  }

  /**
   * {@inheritdoc}
   */
  public function userIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_OK;

    /** @var User|null $user */
    $user = $this->user_manager->find($id);

    if (null === $user)
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    return $this->getBasicUserDataResponse($user);
  }

  /**
   * {@inheritdoc}
   */
  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    $update_error_response = $this->validateUpdateRequest($update_user_request);

    if ($this->isUpdateRequestInvalid($update_error_response))
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return $update_error_response;
    }

    $responseCode = Response::HTTP_NO_CONTENT;

    if (!$update_user_request->isDryRun())
    {
      $this->updateUser($update_user_request);
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function usersSearchGet(string $query, ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null): array
  {
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $responseCode = Response::HTTP_OK;

    if ('' === $query || ctype_space($query))
    {
      return [];
    }

    $users = $this->user_manager->search($query, $limit, $offset);

    return $this->getUsersDataResponse($users);
  }

  /**
   * Validates the Register object passed by the request. No automatic validation provided by the OpenApi
   * will be used cause non standard validations (e.g. validation if a username doesn't exist already) must be
   * used here.
   *
   * $accept_language -> The language used for translating the validation error messages
   *
   * @return RegisterErrorResponse The RegisterErrorResponse containing possible validation errors
   */
  private function validateRegistration(RegisterRequest $register_request): RegisterErrorResponse
  {
    $response = new RegisterErrorResponse();

    // E-Mail
    if (0 === strlen($register_request->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailMissing', [], 'catroweb'));
    }
    elseif (0 !== count($this->validator->validate($register_request->getEmail(), new Email())))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailInvalid', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByEmail($register_request->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailAlreadyInUse', [], 'catroweb'));
    }

    // Username
    if (0 === strlen($register_request->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameMissing', [], 'catroweb'));
    }
    elseif (strlen($register_request->getUsername()) < 3)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooShort', [], 'catroweb'));
    }
    elseif (strlen($register_request->getUsername()) > 180)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooLong', [], 'catroweb'));
    }
    elseif (filter_var(str_replace(' ', '', $register_request->getUsername()), FILTER_VALIDATE_EMAIL))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameContainsEmail', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByUsername($register_request->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameAlreadyInUse', [], 'catroweb'));
    }
    elseif (0 === strncasecmp($register_request->getUsername(), User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX)))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameInvalid', [], 'catroweb'));
    }

    // Password
    if (0 === strlen($register_request->getPassword()))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordMissing', [], 'catroweb'));
    }
    elseif (strlen($register_request->getPassword()) < 6)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooShort', [], 'catroweb'));
    }
    elseif (strlen($register_request->getPassword()) > 4_096)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooLong', [], 'catroweb'));
    }
    elseif (!mb_detect_encoding($register_request->getPassword(), 'UTF-8', true))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb'));
    }

    return $response;
  }

  private function getBasicUserDataResponse(User $user): BasicUserDataResponse
  {
    return new BasicUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ]);
  }

  private function getExtendedUserDataResponse(User $user): BasicUserDataResponse
  {
    return new ExtendedUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'email' => $user->getEmail(),
      'country' => $user->getCountry(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ]);
  }

  private function getUsersDataResponse(array $users): array
  {
    $users_data_response = [];
    foreach ($users as $user)
    {
      $user_data = $this->getBasicUserDataResponse($user);
      $users_data_response[] = $user_data;
    }

    return $users_data_response;
  }

  private function validateUpdateRequest(UpdateUserRequest $update_user_request): UpdateUserErrorResponse
  {
    $response = new UpdateUserErrorResponse();

    if (0 !== strlen($update_user_request->getCountry()))
    {
      $validate_country = $this->validateCountry($update_user_request->getCountry());
      if (!empty($validate_country))
      {
        $response->setCountry($validate_country);
      }
    }

    if (0 !== strlen($update_user_request->getEmail()) || !is_null($update_user_request->getEmail()))
    {
      $validate_email = $this->validateEmail($update_user_request->getEmail());
      if (!empty($validate_email))
      {
        $response->setEmail($validate_email);
      }
    }

    if (0 !== strlen($update_user_request->getUsername()) || !is_null($update_user_request->getUsername()))
    {
      $validate_username = $this->validateUsername($update_user_request->getUsername());
      if (!empty($validate_username))
      {
        $response->setUsername($validate_username);
      }
    }

    if (0 !== strlen($update_user_request->getPassword()) || !is_null($update_user_request->getPassword()))
    {
      $validate_password = $this->validatePassword($update_user_request->getPassword());
      if (!empty($validate_password))
      {
        $response->setPassword($validate_password);
      }
    }

    return $response;
  }

  private function isUpdateRequestInvalid(UpdateUserErrorResponse $error_response): bool
  {
    return $error_response->getUsername()
      || $error_response->getEmail()
      || $error_response->getPassword()
      || $error_response->getCountry();
  }

  private function validateEmail(string $email): string
  {
    $validation = '';

    if (0 === strlen($email))
    {
      $validation = $this->translator->trans('api.registerUser.emailEmpty', [], 'catroweb');
    }
    elseif (0 !== count($this->validator->validate($email, new Email())))
    {
      $validation = $this->translator->trans('api.registerUser.emailInvalid', [], 'catroweb');
    }
    elseif (null != $this->user_manager->findUserByEmail($email))
    {
      $validation = $this->translator->trans('api.registerUser.emailAlreadyInUse', [], 'catroweb');
    }

    return $validation;
  }

  private function validateUsername(string $username): string
  {
    $validation = '';

    if (0 === strlen($username))
    {
      $validation = $this->translator->trans('api.registerUser.usernameEmpty', [], 'catroweb');
    }
    elseif (strlen($username) < 3)
    {
      $validation = $this->translator->trans('api.registerUser.usernameTooShort', [], 'catroweb');
    }
    elseif (strlen($username) > 180)
    {
      $validation = $this->translator->trans('api.registerUser.usernameTooLong', [], 'catroweb');
    }
    elseif (filter_var(str_replace(' ', '', $username), FILTER_VALIDATE_EMAIL))
    {
      $validation = $this->translator->trans('api.registerUser.usernameContainsEmail', [], 'catroweb');
    }
    elseif (null != $this->user_manager->findUserByUsername($username))
    {
      $validation = $this->translator->trans('api.registerUser.usernameAlreadyInUse', [], 'catroweb');
    }
    elseif (0 === strncasecmp($username, User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX)))
    {
      $validation = $this->translator->trans('api.registerUser.usernameInvalid', [], 'catroweb');
    }

    return $validation;
  }

  private function validatePassword(string $password): string
  {
    $validation = '';

    if (0 === strlen($password))
    {
      $validation = $this->translator->trans('api.registerUser.passwordEmpty', [], 'catroweb');
    }
    elseif (strlen($password) < 6)
    {
      $validation = $this->translator->trans('api.registerUser.passwordTooShort', [], 'catroweb');
    }
    elseif (strlen($password) > 4_096)
    {
      $validation = $this->translator->trans('api.registerUser.passwordTooLong', [], 'catroweb');
    }
    elseif (!mb_detect_encoding($password, 'ASCII', true))
    {
      $validation = $this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb');
    }

    return $validation;
  }

  private function validateCountry(string $country): string
  {
    $validation = '';

    if (!Countries::exists($country))
    {
      $validation = $this->translator->trans('api.registerUser.countryCodeInvalid', [], 'catroweb');
    }

    return $validation;
  }

  private function updateUser(UpdateUserRequest $update_user_request)
  {
    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    if (!empty($update_user_request->getEmail()))
    {
      $user->setEmail($update_user_request->getEmail());
    }
    if (!empty($update_user_request->getUsername()))
    {
      $user->setUsername($update_user_request->getUsername());
    }
    if (!empty($update_user_request->getPassword()))
    {
      $user->setPassword($update_user_request->getPassword());
    }
    if (!is_null($update_user_request->getCountry()))
    {
      $user->setCountry($update_user_request->getCountry());
    }

    $this->user_manager->updateUser($user, true);
  }
}
