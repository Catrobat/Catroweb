<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TurnRightSpeedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TURN_RIGHT_SPEED_BRICK;
    $this->caption = 'Rotate right _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
