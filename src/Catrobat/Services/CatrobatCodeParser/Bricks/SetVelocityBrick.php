<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetVelocityBrick.
 */
class SetVelocityBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_VELOCITY_BRICK;
    $this->caption = 'Set velocity to X: _ Y: _ steps/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
