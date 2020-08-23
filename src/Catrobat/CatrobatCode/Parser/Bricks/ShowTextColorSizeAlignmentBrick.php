<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ShowTextColorSizeAlignmentBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK;
    $this->caption = 'Show variable _ at X: _ Y: _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
