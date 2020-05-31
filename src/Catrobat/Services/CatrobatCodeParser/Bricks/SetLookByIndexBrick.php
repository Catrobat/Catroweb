<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetLookByIndexBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_LOOK_BY_INDEX_BRICK;
    $this->caption = 'Switch to look number';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
