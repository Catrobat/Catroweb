<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class TurnRightSpeedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TURN_RIGHT_SPEED_BRICK;
    $this->caption = 'Rotate right _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
