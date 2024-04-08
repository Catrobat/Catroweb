<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class GlideToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GLIDE_TO_BRICK;
    $this->caption = 'Glide _ second(s) to X: _ Y: _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
