<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ShowTextBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SHOW_TEXT_BRICK;
    $this->caption = 'Show variable _ at X: _ Y: _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
