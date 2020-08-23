<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetLookByIndexBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BY_INDEX_BRICK;
    $this->caption = 'Switch to look number';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
