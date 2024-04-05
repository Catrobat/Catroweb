<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TouchAndSlideBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TOUCH_AND_SLIDE_BRICK;
    $this->caption = 'Touch and slide';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
