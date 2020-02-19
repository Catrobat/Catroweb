<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ReadVariableFromDeviceBrick.
 */
class ReadVariableFromDeviceBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::READ_VARIABLE_FROM_DEVICE_BRICK;
    $this->caption = 'Read variable from device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
