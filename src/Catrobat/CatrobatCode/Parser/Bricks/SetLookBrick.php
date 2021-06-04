<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BRICK;
    $this->caption = 'Switch to look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
