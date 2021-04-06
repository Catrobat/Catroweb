<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TouchAndSlideBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TOUCH_AND_SLIDE_BRICK;
    $this->caption = 'Touch and slide';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
