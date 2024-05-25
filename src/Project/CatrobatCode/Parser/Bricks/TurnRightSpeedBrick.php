<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TurnRightSpeedBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::TURN_RIGHT_SPEED_BRICK;
    $this->caption = 'Rotate right _ degrees/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
