<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneEmergencyBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_EMERGENCY_BRICK;
    $this->caption = 'Emergency';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
