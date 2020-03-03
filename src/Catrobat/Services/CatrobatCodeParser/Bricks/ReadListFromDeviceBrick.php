<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ReadListFromDeviceBrick.
 */
class ReadListFromDeviceBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::READ_LIST_FROM_DEVICE_BRICK;
    $this->caption = 'Read list from device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
