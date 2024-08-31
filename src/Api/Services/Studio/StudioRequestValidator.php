<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\ValidationWrapper;
use App\DB\Entity\Studio\Studio;
use App\Studio\StudioManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudioRequestValidator extends AbstractRequestValidator
{
  protected const string MODE_CREATE = 'create_mode';
  protected const string MODE_UPDATE = 'update_mode';

  protected const int MIN_NAME_LENGTH = 3;

  protected const int MAX_NAME_LENGTH = 180;

  protected const int MIN_DESCRIPTION_LENGTH = 1;

  protected const int MAX_DESCRIPTION_LENGTH = 3000;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, private readonly StudioManager $studio_manager)
  {
    parent::__construct($validator, $translator);
  }

  public function validateUpdate(Studio $studio, ?string $name, ?string $description, ?UploadedFile $image_file, string $accept_language): ValidationWrapper
  {
    $this->validateName($name, $accept_language, self::MODE_UPDATE, $studio->getName());
    $this->validateDescription($description, $accept_language, self::MODE_UPDATE);
    $this->validateImageFile($image_file, $accept_language);

    return $this->getValidationWrapper();
  }

  public function validateCreate(?string $name, ?string $description, ?UploadedFile $image_file, string $accept_language): ValidationWrapper
  {
    $this->validateName($name, $accept_language, self::MODE_CREATE);
    $this->validateDescription($description, $accept_language, self::MODE_CREATE);
    $this->validateImageFile($image_file, $accept_language);

    return $this->getValidationWrapper();
  }

  private function validateName(?string $name, string $locale, string $mode, ?string $currentName = null): void
  {
    $KEY = 'name';

    if (is_null($name)) {
      if (self::MODE_CREATE === $mode) {
        $this->getValidationWrapper()->addError($this->__('api.createStudio.nameMissing', [], $locale), $KEY);
      }

      return;
    }

    if ('' === trim($name)) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.nameEmpty', [], $locale), $KEY);
    } elseif (strlen($name) < self::MIN_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.nameTooShort', [], $locale), $KEY);
    } elseif (strlen($name) > self::MAX_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.nameTooLong', [], $locale), $KEY);
    } elseif ($name !== $currentName && null != $this->studio_manager->findStudioByName($name)) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.nameAlreadyInUse', [], $locale), $KEY);
    }
  }

  private function validateDescription(?string $description, string $locale, string $mode): void
  {
    $KEY = 'description';

    if (is_null($description)) {
      if (self::MODE_CREATE === $mode) {
        $this->getValidationWrapper()->addError($this->__('api.createStudio.descriptionMissing', [], $locale), $KEY);
      }

      return;
    }

    if ('' === trim($description)) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.descriptionEmpty', [], $locale), $KEY);
    } elseif (strlen($description) < self::MIN_DESCRIPTION_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.descriptionTooShort', [], $locale), $KEY);
    } elseif (strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.descriptionTooLong', [], $locale), $KEY);
    }
  }

  private function validateImageFile(?UploadedFile $image_file, string $locale): void
  {
    if (null === $image_file) {
      return;
    }

    $KEY = 'image_file';
    $maxFileSize = 1048576; // 1MB in bytes
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!$image_file->isValid()) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.imageFileInvalid', [], $locale), $KEY);
    } elseif (!in_array($image_file->getMimeType(), $allowedMimeTypes, true)) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.imageFileInvalidType', [], $locale), $KEY);
    } elseif ($image_file->getSize() > $maxFileSize) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.imageFileTooLarge', [], $locale), $KEY);
    }

    try {
      $imagick = new \Imagick($image_file->getPathname());
      $imagick->valid(); // Validate the image file
      $imagick->clear();
      $imagick->destroy();
    } catch (\ImagickException) {
      $this->getValidationWrapper()->addError($this->__('api.createStudio.imageFileInvalid', [], $locale), $KEY);

      return;
    }
  }
}
