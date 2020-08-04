<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DroneEmergencyBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_EMERGENCY_BRICK;
    $this->caption = 'Emergency';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
