<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneTurnLeftBrick.
 */
class DroneTurnLeftBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_TURN_LEFT_BRICK;
    $this->caption = 'TURN the drone LEFT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
