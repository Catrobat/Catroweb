<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneMoveUpBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_MOVE_UP_BRICK;
    $this->caption = 'MOVE the drone UP for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
