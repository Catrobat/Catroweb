<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetPenSizeBrick.
 */
class SetPenSizeBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_PEN_SIZE_BRICK;
    $this->caption = 'Set pen size to _';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
