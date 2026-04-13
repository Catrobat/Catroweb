<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Moderation\TextSanitizer;
use App\User\UserAvatarService;
use App\User\UserManager;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

class UserApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly TextSanitizer $textSanitizer,
    private readonly UserAvatarService $user_avatar_service,
  ) {
  }

  /**
   * @throws \Exception
   */
  public function registerUser(RegisterRequest $request): User
  {
    $user = $this->user_manager->create();
    $user->setUsername($request->getUsername());
    $user->setEmail($request->getEmail());
    $user->setPlainPassword($request->getPassword());
    $user->setEnabled(true);
    $user->setVerified(false);

    $picture = $request->getPicture();
    if (null !== $picture && '' !== $picture && '0' !== $picture) {
      // Resize + safety scan happen once in UserRequestValidator::validateAndResizePicture.
      $user->setAvatar($picture);
      $this->user_avatar_service->storeFromDataUri($user, $picture);
    }

    if (null !== $request->getAbout() && '' !== $request->getAbout() && '0' !== $request->getAbout()) {
      $user->setAbout($this->textSanitizer->sanitize($request->getAbout()));
    }

    if (null !== $request->getCurrentlyWorkingOn() && '' !== $request->getCurrentlyWorkingOn() && '0' !== $request->getCurrentlyWorkingOn()) {
      $user->setCurrentlyWorkingOn($this->textSanitizer->sanitize($request->getCurrentlyWorkingOn()));
    }

    $dobString = $request->getDateOfBirth();
    $dob = (null !== $dobString && '' !== $dobString)
      ? \DateTimeImmutable::createFromFormat('Y-m-d|', $dobString) : false;

    if (false !== $dob) {
      $user->setDateOfBirth(\DateTime::createFromImmutable($dob));
      $age = $dob->diff(new \DateTimeImmutable('today'))->y;
      $user->setMinor($age < 16);

      $needsConsent = $age < UserRequestValidator::PARENTAL_CONSENT_AGE;
      $user->setConsentStatus($needsConsent ? 'pending' : 'not_required');

      $parentEmail = $request->getParentEmail();
      if ($needsConsent && null !== $parentEmail && '' !== $parentEmail) {
        $user->setParentEmail($parentEmail);
      }
    }

    $this->user_manager->updateUser($user);

    return $user;
  }

  public function deleteUser(User $user): void
  {
    $this->user_manager->delete($user);
  }

  public function updateUser(User $user, UpdateUserRequest $request): void
  {
    if (null !== $request->getEmail() && '' !== $request->getEmail() && '0' !== $request->getEmail()) {
      $user->setEmail($request->getEmail());
    }

    if (null !== $request->getUsername() && '' !== $request->getUsername() && '0' !== $request->getUsername()) {
      $user->setUsername($request->getUsername());
    }

    if (null !== $request->getPassword() && '' !== $request->getPassword() && '0' !== $request->getPassword()) {
      $user->setPlainPassword($request->getPassword());
    }

    $picture = $request->getPicture();
    if (null !== $picture) {
      if ('' === $picture || '0' === $picture) {
        $user->setAvatar(null);
        $this->user_avatar_service->clearStoredAvatar($user);
      } else {
        // Resize + safety scan happen once in UserRequestValidator::validateAndResizePicture.
        $user->setAvatar($picture);
        $this->user_avatar_service->storeFromDataUri($user, $picture);
      }
    }

    if (!is_null($request->getAbout())) {
      $user->setAbout($this->textSanitizer->sanitize($request->getAbout()));
    }

    if (!is_null($request->getCurrentlyWorkingOn())) {
      $user->setCurrentlyWorkingOn($this->textSanitizer->sanitize($request->getCurrentlyWorkingOn()));
    }

    $this->user_manager->updateUser($user);
  }
}
