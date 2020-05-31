<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class LoopEndlessBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LOOP_ENDLESS_BRICK;
    $this->caption = 'LoopEndlessBrick (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_BRICK_IMG);
  }
}
