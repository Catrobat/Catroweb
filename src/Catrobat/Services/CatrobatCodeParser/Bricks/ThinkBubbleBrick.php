<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ThinkBubbleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::THINK_BUBBLE_BRICK;
    $this->caption = 'Think _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
