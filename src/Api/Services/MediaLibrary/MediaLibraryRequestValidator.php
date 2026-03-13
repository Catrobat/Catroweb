<?php

declare(strict_types=1);

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractRequestValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaLibraryRequestValidator extends AbstractRequestValidator
{
  public function validateFile(UploadedFile $file, string $mime_type): array
  {
    $errors = [];
    $max_size = 10 * 1024 * 1024; // 10MB

    if ($file->getSize() > $max_size) {
      $errors[] = 'File size exceeds 10MB limit';
    }

    $allowed_mime_types = [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/webp',
      'image/svg+xml',
      'image/bmp',
      'audio/mpeg',
      'audio/mp3',
      'audio/wav',
      'audio/ogg',
      'audio/flac',
      'audio/aac',
      'audio/x-wav',
      'audio/x-m4a',
    ];

    if (!in_array($mime_type, $allowed_mime_types, true)) {
      $errors[] = sprintf('Invalid file type - %s', $mime_type);
    }

    return $errors;
  }

  public function validateTranslations(array $translations): array
  {
    $errors = [];

    if ([] === $translations) {
      $errors[] = 'At least one translation is required';

      return $errors;
    }

    foreach ($translations as $locale => $value) {
      if (!is_string($locale) || 2 !== strlen($locale)) {
        $errors[] = sprintf('Invalid locale code: %s', $locale);
      }

      if (!is_string($value) || '' === trim($value)) {
        $errors[] = sprintf('Translation value for locale "%s" cannot be empty', $locale);
      }
    }

    return $errors;
  }
}
