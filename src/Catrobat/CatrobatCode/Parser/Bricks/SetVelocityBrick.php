<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetVelocityBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_VELOCITY_BRICK;
    $this->caption = 'Set velocity to X: _ Y: _ steps/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
