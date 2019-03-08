<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneEmergencyBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class DroneEmergencyBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_EMERGENCY_BRICK;
    $this->caption = "Emergency";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}