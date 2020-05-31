<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BRICK;
    $this->caption = 'Switch to look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
