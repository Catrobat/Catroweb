<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneTurnLeftBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TURN_LEFT_BRICK;
    $this->caption = 'TURN the drone LEFT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
