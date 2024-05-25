<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DronePlayedAnimationBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_PLAYED_ANIMATION_BRICK;
    $this->caption = 'Played drone animation';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
