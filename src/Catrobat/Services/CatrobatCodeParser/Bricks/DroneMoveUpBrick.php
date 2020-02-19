<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneMoveUpBrick.
 */
class DroneMoveUpBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_UP_BRICK;
    $this->caption = 'MOVE the drone UP for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
