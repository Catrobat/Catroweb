<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetGravityBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_GRAVITY_BRICK;
    $this->caption = 'Set gravity for all objects to X: _ Y: _ steps/second²';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
