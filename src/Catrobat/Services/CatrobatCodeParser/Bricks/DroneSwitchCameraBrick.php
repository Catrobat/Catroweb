<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneSwitchCameraBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class DroneSwitchCameraBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_SWITCH_CAMERA_BRICK;
    $this->caption = "Switch Camera";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}