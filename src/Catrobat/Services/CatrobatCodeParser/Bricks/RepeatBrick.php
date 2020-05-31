<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class RepeatBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = 'Repeat _ times';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
