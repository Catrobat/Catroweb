<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetPenSizeBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_PEN_SIZE_BRICK;
    $this->caption = 'Set pen size to _';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
