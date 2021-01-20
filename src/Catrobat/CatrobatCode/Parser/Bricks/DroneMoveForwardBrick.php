<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneMoveForwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_MOVE_FORWARD_BRICK;
    $this->caption = 'MOVE the drone FORWARD for_ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
