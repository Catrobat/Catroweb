<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneTakeOffLandBrick.
 */
class DroneTakeOffLandBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
    $this->caption = 'Take off or land drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
