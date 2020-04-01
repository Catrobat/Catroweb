<?php

namespace App\Api;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\Register;
use OpenAPI\Server\Model\ValidationSchema;
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

  public function __construct(ValidatorInterface $validator, UserManager $user_manager, TokenGenerator $token_generator,
                              TranslatorInterface $translator)
  {
    $this->validator = $validator;
    $this->user_manager = $user_manager;
    $this->token_generator = $token_generator;
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value): void
  {
    $this->token = preg_split('#\s+#', $value)[1];
  }

  /**
   * {@inheritdoc}
   */
  public function userPost(Register $register, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $validation_schema = $this->validate($register);

    if ($validation_schema->getEmail() || $validation_schema->getUsername() || $validation_schema->getPassword())
    {
      $responseCode = 422; // 422 => Unprocessable entity
      return $validation_schema;
    }
    if ($register->isDryRun())
    {
      $responseCode = 204; // 204 => Dry-run successful, no validation error
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
      $responseCode = 201; // 201 => User successfully registered
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
   * @return ValidationSchema The ValidationSchema containing possible validation errors
   */
  public function validate(Register $register): ValidationSchema
  {
    $validation_schema = new ValidationSchema();

    // E-Mail
    if ('' === $register->getEmail())
    {
      $validation_schema->setEmail($this->translator->trans('api.registerUser.emailMissing', [], 'catroweb'));
    }
    elseif (0 !== count($this->validator->validate($register->getEmail(), new Email())))
    {
      $validation_schema->setEmail($this->translator->trans('api.registerUser.notAValidEmail', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByEmail($register->getEmail()))
    {
      $validation_schema->setEmail($this->translator->trans('api.registerUser.emailAlreadyInUse', [], 'catroweb'));
    }

    // Username
    if ('' === $register->getUsername())
    {
      $validation_schema->setUsername($this->translator->trans('api.registerUser.usernameMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) < 3)
    {
      $validation_schema->setUsername($this->translator->trans('api.registerUser.usernameTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) > 180)
    {
      $validation_schema->setUsername($this->translator->trans('api.registerUser.usernameTooLong', [], 'catroweb'));
    }
    elseif (filter_var(str_replace(' ', '', $register->getUsername()), FILTER_VALIDATE_EMAIL))
    {
      $validation_schema->setUsername($this->translator->trans('api.registerUser.usernameContainsEmail', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByUsername($register->getUsername()))
    {
      $validation_schema->setUsername($this->translator->trans('api.registerUser.usernameAlreadyInUse', [], 'catroweb'));
    }

    // Password
    if ('' === $register->getPassword())
    {
      $validation_schema->setPassword($this->translator->trans('api.registerUser.passwordMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) < 6)
    {
      $validation_schema->setPassword($this->translator->trans('api.registerUser.passwordTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) > 4_096)
    {
      $validation_schema->setPassword($this->translator->trans('api.registerUser.passwordTooLong', [], 'catroweb'));
    }
    elseif (!mb_detect_encoding($register->getPassword(), 'ASCII', true))
    {
      $validation_schema->setPassword($this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb'));
    }

    return $validation_schema;
  }
}
