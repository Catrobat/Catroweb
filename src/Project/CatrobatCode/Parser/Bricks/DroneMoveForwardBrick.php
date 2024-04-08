<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneMoveForwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_MOVE_FORWARD_BRICK;
    $this->caption = 'MOVE the drone FORWARD for_ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
