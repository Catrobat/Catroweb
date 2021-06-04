<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetBrightnessBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BRIGHTNESS_BRICK;
    $this->caption = 'Set brightness to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
