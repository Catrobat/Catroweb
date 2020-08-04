<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LoopEndlessBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LOOP_ENDLESS_BRICK;
    $this->caption = 'LoopEndlessBrick (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_BRICK_IMG);
  }
}
