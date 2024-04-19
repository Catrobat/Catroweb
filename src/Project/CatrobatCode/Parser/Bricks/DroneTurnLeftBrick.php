<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneTurnLeftBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TURN_LEFT_BRICK;
    $this->caption = 'TURN the drone LEFT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
