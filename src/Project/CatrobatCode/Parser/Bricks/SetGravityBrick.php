<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetGravityBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_GRAVITY_BRICK;
    $this->caption = 'Set gravity for all objects to X: _ Y: _ steps/second²';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
