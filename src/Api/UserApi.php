<?php

namespace App\Api;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\APIHelper;
use Exception;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Email;
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

  public function __construct(ValidatorInterface $validator, UserManager $user_manager, TokenGenerator $token_generator,
                              TranslatorInterface $translator, TokenStorageInterface $token_storage)
  {
    $this->validator = $validator;
    $this->user_manager = $user_manager;
    $this->token_generator = $token_generator;
    $this->translator = $translator;
    $this->token_storage = $token_storage;
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
  public function userPost(RegisterRequest $register_request, string $accept_language = null, &$responseCode, array &$responseHeaders)
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
    }
    else
    {
      // Validation successful, no dry-run requested => we can actually register the user
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setUsername($register_request->getUsername());
      $user->setEmail($register_request->getEmail());
      $user->setPlainPassword($register_request->getPassword());
      $user->setEnabled(true);
      $user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($user);
      $responseCode = Response::HTTP_CREATED; // 201 => User successfully registered
    }

    return null;
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
  public function validateRegistration(RegisterRequest $register_request): RegisterErrorResponse
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
    elseif (!mb_detect_encoding($register_request->getPassword(), 'ASCII', true))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb'));
    }

    return $response;
  }

  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    $this->user_manager->delete($user);

    return null;
  }

  public function userGet(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_OK;

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    return $this->getExtendedUserDataResponse($user);
  }

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

  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    // TODO: Implement userPut() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function usersSearchGet(string $query, ?int $limit = 20, ?int $offset = 0, &$responseCode, array &$responseHeaders)
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

  private function getBasicUserDataResponse(User $user): BasicUserDataResponse
  {
    return new BasicUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'email' => $user->getEmail(),
      'country' => $user->getCountry(),
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
}
