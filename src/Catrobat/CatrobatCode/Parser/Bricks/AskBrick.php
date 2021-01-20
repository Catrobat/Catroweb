<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class AskBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASK_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
