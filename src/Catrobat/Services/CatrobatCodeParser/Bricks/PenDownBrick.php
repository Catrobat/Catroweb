<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PenDownBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PEN_DOWN_BRICK;
    $this->caption = 'Pen down';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
