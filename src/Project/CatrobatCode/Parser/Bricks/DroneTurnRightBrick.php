<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneTurnRightBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TURN_RIGHT_BRICK;
    $this->caption = 'TURN the drone RIGHT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
