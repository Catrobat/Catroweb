<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class TurnLeftSpeedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TURN_LEFT_SPEED_BRICK;
    $this->caption = 'Rotate left _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
