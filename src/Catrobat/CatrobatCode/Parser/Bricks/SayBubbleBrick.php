<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SayBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SAY_BUBBLE_BRICK;
    $this->caption = 'Say _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
