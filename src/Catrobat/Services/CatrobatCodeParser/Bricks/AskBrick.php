<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class AskBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASK_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
