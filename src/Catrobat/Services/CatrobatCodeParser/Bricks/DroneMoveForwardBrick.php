<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneMoveForwardBrick.
 */
class DroneMoveForwardBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_FORWARD_BRICK;
    $this->caption = 'MOVE the drone FORWARD for_ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
