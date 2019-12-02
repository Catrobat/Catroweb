<?php

namespace App\Utils;

use App\Catrobat\StatusCode;
use Exception;


/**
 * Class ImageBase64Checks
 */
class ImageUtils {

  /**
   * @param $image_base64
   * @param $MAX_UPLOAD_SIZE
   * @param $MAX_IMAGE_SIZE
   *
   * @return string
   * @throws Exception
   */
  public static function checkAndResizeBase64Image($image_base64, $MAX_IMAGE_SIZE = 300, $MAX_UPLOAD_SIZE = 5*1024*1024)
  {
    $image_data = explode(';base64,', $image_base64);
    $data_regx = '/data:(.+)/';

    if (!preg_match($data_regx, $image_data[0]))
    {
      throw new Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    $image_type = preg_replace('/data:(.+)/', '\\1', $image_data[0]);
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
        throw new Exception(StatusCode::UPLOAD_UNSUPPORTED_MIME_TYPE);
    }

    if (!$image)
    {
      throw new Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    // https://en.wikipedia.org/wiki/Base64 not exact but enough for our checks
    $image_size = (strlen($image_base64) * (3/4));

    if ($image_size > $MAX_UPLOAD_SIZE)
    {
      throw new Exception(StatusCode::UPLOAD_EXCEEDING_FILESIZE);
    }

    $width = imagesx($image);
    $height = imagesy($image);

    if ($width === 0 || $height === 0)
    {
      throw new Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    if ($MAX_IMAGE_SIZE !== null && max($width, $height) > $MAX_IMAGE_SIZE)
    {
      $new_image = imagecreatetruecolor($MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE);
      if (!$new_image)
      {
        throw new Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      imagesavealpha($new_image, true);
      imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 0, 0, 0, 127));

      if (!imagecopyresized($new_image, $image, 0, 0, 0, 0, $MAX_IMAGE_SIZE, $MAX_IMAGE_SIZE, $width, $height))
      {
        imagedestroy($new_image);
        throw new Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      ob_start();
      if (!imagepng($new_image))
      {
        imagedestroy($new_image);
        throw new Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      $image_base64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }

    return $image_base64;
  }
}
