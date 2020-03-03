<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneMoveBackwardBrick.
 */
class DroneMoveBackwardBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_BACKWARD_BRICK;
    $this->caption = 'MOVE the drone BACKWARD for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
