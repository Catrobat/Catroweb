<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ShowTextColorSizeAlignmentBrick.
 */
class ShowTextColorSizeAlignmentBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SHOW_TEXT_COLOR_SIZE_ALIGNMENT_BRICK;
    $this->caption = 'Show variable _ at X: _ Y: _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
