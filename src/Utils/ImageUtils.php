<?php

namespace App\Utils;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ImageUtils
{
  /**
   * @throws Exception
   */
  public static function checkAndResizeBase64Image(string $image_base64, ?int $MAX_IMAGE_SIZE = 300, int $MAX_UPLOAD_SIZE = 5 * 1_024 * 1_024): string
  {
    $image_data = explode(';base64,', $image_base64);
    $data_regx = '/data:(.+)/';

    if (!preg_match($data_regx, $image_data[0])) {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    $image_type = preg_replace('#data:(.+)#', '\\1', $image_data[0]);
    $image = match ($image_type) {
      'image/jpg', 'image/jpeg' => imagecreatefromjpeg($image_base64),
        'image/png' => imagecreatefrompng($image_base64),
        'image/gif' => imagecreatefromgif($image_base64),
        default => throw new Exception('UPLOAD_UNSUPPORTED_MIME_TYPE', Response::HTTP_UNSUPPORTED_MEDIA_TYPE),
    };

    if (!$image) {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    // https://en.wikipedia.org/wiki/Base64 not exact but enough for our checks
    $image_size = (strlen($image_base64) * (3 / 4));

    if ($image_size > $MAX_UPLOAD_SIZE) {
      throw new Exception('UPLOAD_EXCEEDING_FILESIZE', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    $width = imagesx($image);
    $height = imagesy($image);

    if (0 === $width || 0 === $height) {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    if (null !== $MAX_IMAGE_SIZE && max($width, $height) > $MAX_IMAGE_SIZE) {
      $new_image = imagecreatetruecolor($MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE);
      if (!$new_image) {
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', 814);
      }

      imagesavealpha($new_image, true);
      imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 0, 0, 0, 127));

      if (!imagecopyresized($new_image, $image, 0, 0, 0, 0, $MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE, $width, $height)) {
        imagedestroy($new_image);
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', 814);
      }

      ob_start();
      if (!imagepng($new_image)) {
        imagedestroy($new_image);
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', 814);
      }

      $image_base64 = 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }

    return $image_base64;
  }
}
