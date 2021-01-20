<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ThinkBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::THINK_BUBBLE_BRICK;
    $this->caption = 'Think _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
