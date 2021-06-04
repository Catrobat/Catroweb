<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneMoveDownBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_MOVE_DOWN_BRICK;
    $this->caption = 'MOVE the drone DOWN for_ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
