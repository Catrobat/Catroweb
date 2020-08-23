<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class GlideToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::GLIDE_TO_BRICK;
    $this->caption = 'Glide _ second(s) to X: _ Y: _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
