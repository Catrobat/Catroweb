<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\Entity\User;
use App\Manager\UserManager;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserRequestValidator extends AbstractRequestValidator
{
  public const MIN_PASSWORD_LENGTH = 6;
  public const MAX_PASSWORD_LENGTH = 4096;

  public const MIN_USERNAME_LENGTH = 3;
  public const MAX_USERNAME_LENGTH = 180;

  public const MODE_REGISTER = 'register_mode';
  public const MODE_RESET_PASSWORD = 'reset_password_mode';
  public const MODE_UPDATE = 'update_mode';

  private UserManager $user_manager;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, UserManager $user_manager)
  {
    parent::__construct($validator, $translator);

    $this->user_manager = $user_manager;
  }

  public function validateRegistration(RegisterRequest $request, string $locale): ValidationWrapper
  {
    $this->validateEmail($request->getEmail(), $locale, self::MODE_REGISTER);
    $this->validateUsername($request->getUsername(), $locale, self::MODE_REGISTER);
    $this->validatePassword($request->getPassword(), $locale, self::MODE_REGISTER);

    return $this->getValidationWrapper();
  }

  public function validateUpdateRequest(UpdateUserRequest $request, string $locale): ValidationWrapper
  {
    if (!is_null(($request->getEmail()))) {
      $this->validateEmail($request->getEmail(), $locale, self::MODE_UPDATE);
    }

    if (!is_null(($request->getUsername()))) {
      $this->validateUsername($request->getUsername(), $locale, self::MODE_UPDATE);
    }

    if (!is_null(($request->getPassword()))) {
      $this->validatePassword($request->getPassword(), $locale, self::MODE_UPDATE);
    }

    return $this->getValidationWrapper();
  }

  public function validateResetPasswordRequest(ResetPasswordRequest $request, string $locale): ValidationWrapper
  {
    $this->validateEmail($request->getEmail(), $locale, self::MODE_RESET_PASSWORD);

    return $this->getValidationWrapper();
  }

  private function validateEmail(?string $email, string $locale, string $mode): void
  {
    $KEY = 'email';

    if (self::MODE_UPDATE !== $mode && (is_null($email) || '' === trim($email))) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.emailMissing', [], $locale), $KEY);
    } elseif (self::MODE_UPDATE === $mode && '' === trim($email)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.emailEmpty', [], $locale), $KEY);
    } elseif (0 !== count($this->validate($email, new Email()))) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.emailInvalid', [], $locale), $KEY);
    } elseif (self::MODE_RESET_PASSWORD !== $mode && null != $this->user_manager->findUserByEmail($email)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.emailAlreadyInUse', [], $locale), $KEY);
    }
  }

  private function validateUsername(?string $username, string $locale, string $mode): void
  {
    $KEY = 'username';

    if (self::MODE_UPDATE !== $mode && (is_null($username) || '' === trim($username))) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameMissing', [], $locale), $KEY);
    } elseif (self::MODE_UPDATE === $mode && '' === trim($username)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameEmpty', [], $locale), $KEY);
    } elseif (strlen($username) < self::MIN_USERNAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameTooShort', [], $locale), $KEY);
    } elseif (strlen($username) > self::MAX_USERNAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameTooLong', [], $locale), $KEY);
    } elseif (filter_var(str_replace(' ', '', $username), FILTER_VALIDATE_EMAIL)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameContainsEmail', [], $locale), $KEY);
    } elseif (null != $this->user_manager->findUserByUsername($username)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameAlreadyInUse', [], $locale), $KEY);
    } elseif (0 === strncasecmp($username, User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX))) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.usernameInvalid', [], $locale), $KEY);
    }
  }

  private function validatePassword(?string $password, string $locale, string $mode): void
  {
    $KEY = 'password';

    if (self::MODE_UPDATE !== $mode && (is_null($password) || '' === trim($password))) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.passwordMissing', [], $locale), $KEY);
    } elseif (self::MODE_UPDATE === $mode && '' === trim($password)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.passwordEmpty', [], $locale), $KEY);
    } elseif (strlen($password) < self::MIN_PASSWORD_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.passwordTooShort', [], $locale), $KEY);
    } elseif (strlen($password) > self::MAX_PASSWORD_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.passwordTooLong', [], $locale), $KEY);
    } elseif (!mb_detect_encoding($password, 'ASCII', true)) {
      $this->getValidationWrapper()->addError($this->__('api.registerUser.passwordInvalidChars', [], $locale), $KEY);
    }
  }
}
