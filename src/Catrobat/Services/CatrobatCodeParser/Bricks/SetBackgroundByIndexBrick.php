<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetBackgroundByIndexBrick.
 */
class SetBackgroundByIndexBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_BACKGROUND_BY_INDEX_BRICK;
    $this->caption = 'Set background to number';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
