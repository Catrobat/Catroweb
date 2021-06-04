<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneTakeOffLandBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
    $this->caption = 'Take off or land drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
