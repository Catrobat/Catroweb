<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TurnLeftSpeedBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::TURN_LEFT_SPEED_BRICK;
    $this->caption = 'Rotate left _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
