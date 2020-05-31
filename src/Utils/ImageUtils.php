<?php

namespace App\Utils;

use App\Catrobat\StatusCode;
use Exception;

class ImageUtils
{
  /**
   * @throws Exception
   */
  public static function checkAndResizeBase64Image(string $image_base64, ?int $MAX_IMAGE_SIZE = 300, int $MAX_UPLOAD_SIZE = 5 * 1_024 * 1_024): string
  {
    $image_data = explode(';base64,', $image_base64);
    $data_regx = '/data:(.+)/';

    if (!preg_match($data_regx, $image_data[0]))
    {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    $image_type = preg_replace('#data:(.+)#', '\\1', $image_data[0]);
    $image = null;

    switch ($image_type)
    {
      case 'image/jpg':
      case 'image/jpeg':
        $image = imagecreatefromjpeg($image_base64);
        break;
      case 'image/png':
        $image = imagecreatefrompng($image_base64);
        break;
      case 'image/gif':
        $image = imagecreatefromgif($image_base64);
        break;
      default:
        throw new Exception('UPLOAD_UNSUPPORTED_MIME_TYPE', StatusCode::UPLOAD_UNSUPPORTED_MIME_TYPE);
    }

    if (!$image)
    {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    // https://en.wikipedia.org/wiki/Base64 not exact but enough for our checks
    $image_size = (strlen($image_base64) * (3 / 4));

    if ($image_size > $MAX_UPLOAD_SIZE)
    {
      throw new Exception('UPLOAD_EXCEEDING_FILESIZE', StatusCode::UPLOAD_EXCEEDING_FILESIZE);
    }

    $width = imagesx($image);
    $height = imagesy($image);

    if (0 === $width || 0 === $height)
    {
      throw new Exception('UPLOAD_UNSUPPORTED_FILE_TYPE', StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    if (null !== $MAX_IMAGE_SIZE && max($width, $height) > $MAX_IMAGE_SIZE)
    {
      $new_image = imagecreatetruecolor($MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE);
      if (!$new_image)
      {
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      imagesavealpha($new_image, true);
      imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 0, 0, 0, 127));

      if (!imagecopyresized($new_image, $image, 0, 0, 0, 0, $MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE, $width, $height))
      {
        imagedestroy($new_image);
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      ob_start();
      if (!imagepng($new_image))
      {
        imagedestroy($new_image);
        throw new Exception('USER_AVATAR_UPLOAD_ERROR', StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      $image_base64 = 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }

    return $image_base64;
  }
}
