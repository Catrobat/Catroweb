<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetYBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_Y_BRICK;
    $this->caption = 'Set Y to _';

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
