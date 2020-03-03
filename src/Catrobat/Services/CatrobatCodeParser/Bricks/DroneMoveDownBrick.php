<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneMoveDownBrick.
 */
class DroneMoveDownBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_DOWN_BRICK;
    $this->caption = 'MOVE the drone DOWN for_ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
