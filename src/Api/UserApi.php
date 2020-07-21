<?php

namespace App\Api;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\APIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;
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

  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager, ValidatorInterface $validator, UserManager $user_manager, TokenGenerator $token_generator,
                              TranslatorInterface $translator)
  {
    $this->entity_manager = $entity_manager;
    $this->validator = $validator;
    $this->user_manager = $user_manager;
    $this->token_generator = $token_generator;
    $this->translator = $translator;
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
  public function userPost(RegisterRequest $register, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    $validation_schema = $this->validateRegistration($register);

    if ($validation_schema->getEmail() || $validation_schema->getUsername() || $validation_schema->getPassword())
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => Unprocessable entity

      return $validation_schema;
    }
    if ($register->isDryRun())
    {
      $responseCode = Response::HTTP_NO_CONTENT; // 204 => Dry-run successful, no validation error
    }
    else
    {
      // Validation successful, no dry-run requested => we can actually register the user
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setUsername($register->getUsername());
      $user->setEmail($register->getEmail());
      $user->setPlainPassword($register->getPassword());
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
  public function validateRegistration(RegisterRequest $register): RegisterErrorResponse
  {
    $response = new RegisterErrorResponse();

    // E-Mail
    if (0 === strlen($register->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailMissing', [], 'catroweb'));
    }
    elseif (0 !== count($this->validator->validate($register->getEmail(), new Email())))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailInvalid', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByEmail($register->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailAlreadyInUse', [], 'catroweb'));
    }

    // Username
    if (0 === strlen($register->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) < 3)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) > 180)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooLong', [], 'catroweb'));
    }
    elseif (filter_var(str_replace(' ', '', $register->getUsername()), FILTER_VALIDATE_EMAIL))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameContainsEmail', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByUsername($register->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameAlreadyInUse', [], 'catroweb'));
    }
    elseif (0 === strncasecmp($register->getUsername(), User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX)))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameInvalid', [], 'catroweb'));
    }

    // Password
    if (0 === strlen($register->getPassword()))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) < 6)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) > 4_096)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooLong', [], 'catroweb'));
    }
    elseif (!mb_detect_encoding($register->getPassword(), 'ASCII', true))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb'));
    }

    return $response;
  }

  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    $user = $this->getCurrentUser();
    $this->user_manager->deleteUser($user);
    $responseCode = Response::HTTP_OK;

    return null;
  }

  public function userGet(&$responseCode, array &$responseHeaders)
  {
    $user = $this->getCurrentUser();
    $responseCode = Response::HTTP_OK;

    return new ExtendedUserDataResponse($this->getUserDataResponse($user));
  }

  public function userIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $user = $this->entity_manager->getRepository(User::class)
      ->findOneBy(['id' => $id])
      ;

    if (null === $user)
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return new ExtendedUserDataResponse($this->getUserDataResponse($user));
  }

  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    /*  $validation_schema = $this->validateRegistration($update_user_request);

     if ($validation_schema->getEmail() || $validation_schema->getUsername() || $validation_schema->getPassword())
      {
          $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => Unprocessable entity

          return $validation_schema;
      }*/
    if ($update_user_request->isDryRun())
    {
      $responseCode = Response::HTTP_NO_CONTENT; // 204 => Dry-run successful, no validation error
    }
    else
    {
      $user = $this->getCurrentUser();
      // Validation successful, no dry-run requested => we can actually register the user
      $user->setUsername($update_user_request->getUsername());
      $user->setEmail($update_user_request->getEmail());
      $user->setPlainPassword($update_user_request->getPassword());
      $user->setEnabled(true);
      $user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($user);
      $responseCode = Response::HTTP_CREATED; // 201 => User successfully registered
    }

    return null;
  }

  public function usersSearchGet(string $query, int $limit = 20, int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    $qb = $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->where('u.username LIKE :query')
      ->from('App\Entity\User', 'u')
      ->setParameter('query', '%'.$query.'%')
      ->orderBy('u.username', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ;
    $users = $qb->getQuery()->getResult();
    $users_data_response = [];

    foreach ($users as $user)
    {
      $users_data_response[] = new ExtendedUserDataResponse($this->getUserDataResponse($user));
    }

    return $users_data_response;
  }

  private function getUserDataResponse(User $user): array
  {
    return $user = [
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'email' => $user->getEmail(),
      'image' => $user->getAvatar(),
      'country' => $user->getCountry(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ];
  }

  private function getCurrentUser(): object
  {
    $jwtPayload = $this->user_manager->decodeToken($this->token);
    if (!array_key_exists('username', $jwtPayload))
    {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    return $this->entity_manager->getRepository(User::class)
      ->findOneBy(['username' => $jwtPayload['username']])
        ;
  }
}
