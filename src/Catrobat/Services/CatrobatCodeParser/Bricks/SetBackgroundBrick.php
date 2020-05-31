<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetBackgroundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BACKGROUND_BRICK;
    $this->caption = 'Set background';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
