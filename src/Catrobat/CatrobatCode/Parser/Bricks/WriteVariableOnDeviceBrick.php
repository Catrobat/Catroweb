<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WriteVariableOnDeviceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WRITE_VARIABLE_ON_DEVICE_BRICK;
    $this->caption = 'Write variable on device _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
