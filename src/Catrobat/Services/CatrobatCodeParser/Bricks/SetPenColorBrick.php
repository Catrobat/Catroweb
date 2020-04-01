<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetPenColorBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_PEN_COLOR_BRICK;
    $this->caption = 'Set pen color to Red: _ Green: _ Blue: _';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
