<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetVelocityBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_VELOCITY_BRICK;
    $this->caption = 'Set velocity to X: _ Y: _ steps/second';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
