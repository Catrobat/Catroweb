<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneMoveRightBrick.
 */
class DroneMoveRightBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_RIGHT_BRICK;
    $this->caption = 'MOVE the drone RIGHT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
