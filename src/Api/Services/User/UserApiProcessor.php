<?php

declare(strict_types=1);

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\User\User;
use App\Moderation\TextSanitizer;
use App\Storage\Images\ImageVariantGenerator;
use App\User\UserAvatarService;
use App\User\UserManager;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

class UserApiProcessor extends AbstractApiProcessor
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly TextSanitizer $textSanitizer,
    private readonly ImageVariantGenerator $image_variant_generator,
    private readonly UserAvatarService $user_avatar_service,
  ) {
  }

  /**
   * Decodes the validated+resized data URI, writes variants to the user's
   * storage dir, stamps the basename key on the entity, and clears any
   * previous variant set. The data URI stays on the entity for one release
   * so legacy read-through keeps working while the migration backfills.
   */
  private function storeAvatarFromDataUri(User $user, string $data_uri): void
  {
    if (!preg_match('#^data:([^;]+);base64,(.*)$#s', $data_uri, $matches)) {
      return;
    }

    $decoded = base64_decode((string) $matches[2], true);
    if (false === $decoded) {
      return;
    }

    // Clean up a previous variant set before writing the new one.
    $previous_key = $user->getAvatarKey();
    if (null !== $previous_key && '' !== $previous_key) {
      $this->image_variant_generator->remove($this->user_avatar_service->getStorageDir(), $previous_key);
    }

    $temp_source = tempnam(sys_get_temp_dir(), 'catroweb-avatar-');
    if (false === $temp_source) {
      return;
    }

    try {
      if (false === file_put_contents($temp_source, $decoded)) {
        return;
      }

      $key = (string) $user->getId().'-'.dechex(random_int(0, 0xFFFFFFFF));
      $this->image_variant_generator->generate(
        $temp_source,
        $this->user_avatar_service->getStorageDir(),
        $key,
      );
      $user->setAvatarKey($key);
    } finally {
      @unlink($temp_source);
    }
  }

  private function clearAvatar(User $user): void
  {
    $key = $user->getAvatarKey();
    if (null !== $key && '' !== $key) {
      $this->image_variant_generator->remove($this->user_avatar_service->getStorageDir(), $key);
    }
    $user->setAvatarKey(null);
    $user->setAvatar(null);
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

    if (null !== $request->getPicture() && '' !== $request->getPicture() && '0' !== $request->getPicture()) {
      // Resize + safety scan happen once in UserRequestValidator::validateAndResizePicture;
      // keep the legacy base64 column populated for read-through while the migration runs…
      $user->setAvatar($request->getPicture());
      // …and fan out the variant set on disk.
      $this->storeAvatarFromDataUri($user, $request->getPicture());
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

    if (!is_null($request->getPicture())) {
      if ('' === $request->getPicture() || '0' === $request->getPicture()) {
        $this->clearAvatar($user);
      } else {
        // Resize + safety scan happen once in UserRequestValidator::validateAndResizePicture.
        $user->setAvatar($request->getPicture());
        $this->storeAvatarFromDataUri($user, $request->getPicture());
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
