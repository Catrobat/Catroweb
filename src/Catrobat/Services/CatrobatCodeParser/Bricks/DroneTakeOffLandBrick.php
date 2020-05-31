<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DroneTakeOffLandBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
    $this->caption = 'Take off or land drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
