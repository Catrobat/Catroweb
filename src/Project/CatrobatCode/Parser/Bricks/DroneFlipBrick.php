<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneFlipBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_FLIP_BRICK;
    $this->caption = 'Flip the drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
