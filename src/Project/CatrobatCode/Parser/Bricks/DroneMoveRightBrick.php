<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneMoveRightBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_MOVE_RIGHT_BRICK;
    $this->caption = 'MOVE the drone RIGHT for _ seconds with _ % power';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
