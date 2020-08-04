<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetColorBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_COLOR_BRICK;
    $this->caption = 'Set color to _ %';

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
