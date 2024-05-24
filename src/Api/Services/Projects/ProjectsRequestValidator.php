<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\GeneralValidator;
use App\Api\Services\ValidationWrapper;
use App\DB\Entity\User\User;
use App\User\UserManager;
use OpenAPI\Server\Model\UpdateProjectRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsRequestValidator extends AbstractRequestValidator
{
  final public const int MIN_NAME_LENGTH = 1;

  final public const int MAX_NAME_LENGTH = 255;

  final public const int MAX_DESCRIPTION_LENGTH = 10_000;

  final public const int MAX_CREDITS_LENGTH = 3_000;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, private readonly UserManager $user_manager)
  {
    parent::__construct($validator, $translator);
  }

  public function validateUserExists(string $user_id): bool
  {
    return '' !== trim($user_id)
        && !ctype_space($user_id)
        && $this->user_manager->findOneBy(['id' => $user_id]) instanceof User;
  }

  public function validateUploadFile(string $checksum, UploadedFile $file, string $locale): ValidationWrapper
  {
    $KEY = 'error';

    if (!$file->isValid()) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.upload_error', [], $locale), $KEY
      );
    }

    $calculated_checksum = md5_file($file->getPathname());
    if (strtolower((string) $calculated_checksum) !== strtolower($checksum)) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.invalid_checksum', [], $locale), $KEY
      );
    }

    return $this->getValidationWrapper();
  }

  public function validateUpdateRequest(UpdateProjectRequest $request, string $locale): ValidationWrapper
  {
    if (!is_null($request->getName())) {
      $this->validateName($request->getName(), $locale);
    }

    if (!is_null($request->getDescription())) {
      $this->validateDescription($request->getDescription(), $locale);
    }

    if (!is_null($request->getCredits())) {
      $this->validateCredits($request->getCredits(), $locale);
    }

    if (!is_null($request->getScreenshot())) {
      $this->validateScreenshot($request->getScreenshot(), $locale);
    }

    return $this->getValidationWrapper();
  }

  private function validateName(string $name, string $locale): void
  {
    $KEY = 'name';

    if (strlen($name) < self::MIN_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.nameEmpty', [], $locale), $KEY);
    } elseif (strlen($name) > self::MAX_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.nameTooLong', [], $locale), $KEY);
    }
  }

  private function validateDescription(string $description, string $locale): void
  {
    $KEY = 'description';

    if (strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.descriptionTooLong', [], $locale), $KEY);
    }
  }

  private function validateCredits(string $credits, string $locale): void
  {
    $KEY = 'credits';

    if (strlen($credits) > self::MAX_CREDITS_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.creditsTooLong', [], $locale), $KEY);
    }
  }

  private function validateScreenshot(string $screenshot, string $locale): void
  {
    $KEY = 'screenshot';
    if (false === GeneralValidator::validateImageDataUrl($screenshot)) {
      $this->getValidationWrapper()->addError($this->__('api.project.screenshotInvalid', [], $locale), $KEY);
    }
  }
}
