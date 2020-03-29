<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DroneFlipBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_FLIP_BRICK;
    $this->caption = 'Flip the drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
