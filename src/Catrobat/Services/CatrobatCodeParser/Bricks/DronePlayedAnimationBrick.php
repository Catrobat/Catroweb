<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DronePlayedAnimationBrick.
 */
class DronePlayedAnimationBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_PLAYED_ANIMATION_BRICK;
    $this->caption = 'Played drone animation';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
