<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TurnLeftSpeedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TURN_LEFT_SPEED_BRICK;
    $this->caption = 'Rotate left _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
