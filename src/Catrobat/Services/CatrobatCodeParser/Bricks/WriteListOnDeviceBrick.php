<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WriteListOnDeviceBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class WriteListOnDeviceBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WRITE_LIST_ON_DEVICE_BRICK;
    $this->caption = "Write list on device";
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}