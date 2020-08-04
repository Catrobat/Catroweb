<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ReadVariableFromDeviceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::READ_VARIABLE_FROM_DEVICE_BRICK;
    $this->caption = 'Read variable from device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
