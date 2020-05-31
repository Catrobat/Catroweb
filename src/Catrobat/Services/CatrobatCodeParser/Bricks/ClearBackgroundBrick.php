<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ClearBackgroundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_BACKGROUND_BRICK;
    $this->caption = 'Clear';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
