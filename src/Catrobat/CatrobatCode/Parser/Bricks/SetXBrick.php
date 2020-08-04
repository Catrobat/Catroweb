<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetXBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_X_BRICK;
    $this->caption = 'Set X to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
