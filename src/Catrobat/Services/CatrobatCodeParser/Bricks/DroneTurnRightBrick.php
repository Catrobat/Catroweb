<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DroneTurnRightBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TURN_RIGHT_BRICK;
    $this->caption = 'TURN the drone RIGHT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
