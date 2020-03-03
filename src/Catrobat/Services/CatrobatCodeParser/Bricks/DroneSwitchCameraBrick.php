<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneSwitchCameraBrick.
 */
class DroneSwitchCameraBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_SWITCH_CAMERA_BRICK;
    $this->caption = 'Switch Camera';
    $this->setImgFile(Constants::AR_DRONE_LOOKS_BRICK_IMG);
  }
}
