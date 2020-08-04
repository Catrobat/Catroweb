<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LoopEndBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LOOP_END_BRICK;
    $this->caption = 'End of loop';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
