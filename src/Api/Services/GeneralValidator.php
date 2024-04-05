<?php

declare(strict_types=1);

namespace App\Api\Services;

class GeneralValidator
{
  final public const VALID_PICTURE_MIME_TYPES = ['jpeg', 'png', 'gif', 'webp', 'bmp'];
  final public const VALID_PICTURE_DATA_URL_REGEX = '/^data:image\/([^;]+);base64,([A-Za-z0-9\/+=]+)$/';

  public static function validateImageDataUrl(string $image, bool $jpeg_if_mime_type_not_supported = false): bool|\Imagick
  {
    if (1 === preg_match(self::VALID_PICTURE_DATA_URL_REGEX, $image, $matches)) {
      $image_type = $matches[1];
      if (!$jpeg_if_mime_type_not_supported && !in_array($image_type, self::VALID_PICTURE_MIME_TYPES, true)) {
        return false;
      }

      $image_binary = base64_decode($matches[2], true);
      if (false === $image_binary) {
        return false;
      }
      try {
        $imagick = new \Imagick();
        $imagick->readImageBlob($image_binary);
        if ($jpeg_if_mime_type_not_supported && !in_array($image_type, self::VALID_PICTURE_MIME_TYPES, true)) {
          $imagick->setImageFormat('JPEG');
        }

        return $imagick;
      } catch (\ImagickException) {
        return false;
      }
    } else {
      return false;
    }
  }
}
