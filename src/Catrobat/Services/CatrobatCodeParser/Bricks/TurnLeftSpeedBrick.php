<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class TurnLeftSpeedBrick.
 */
class TurnLeftSpeedBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::TURN_LEFT_SPEED_BRICK;
    $this->caption = 'Rotate left _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
