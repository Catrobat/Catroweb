<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ReadListFromDeviceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::READ_LIST_FROM_DEVICE_BRICK;
    $this->caption = 'Read list from device';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
