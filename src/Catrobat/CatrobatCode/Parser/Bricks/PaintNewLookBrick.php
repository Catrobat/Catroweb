<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PaintNewLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PAINT_NEW_LOOK_BRICK;
    $this->caption = 'Paint new look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
