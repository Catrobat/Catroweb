<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DronePlayedAnimationBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_PLAYED_ANIMATION_BRICK;
    $this->caption = 'Played drone animation';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
