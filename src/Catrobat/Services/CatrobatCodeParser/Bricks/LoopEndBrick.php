<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class LoopEndBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LOOP_END_BRICK;
    $this->caption = 'End of loop';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
