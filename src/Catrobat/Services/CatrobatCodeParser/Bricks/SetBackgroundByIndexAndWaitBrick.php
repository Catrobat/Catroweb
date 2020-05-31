<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetBackgroundByIndexAndWaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BACKGROUND_BY_INDEX_AND_WAIT_BRICK;
    $this->caption = 'Set background to number and wait';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
