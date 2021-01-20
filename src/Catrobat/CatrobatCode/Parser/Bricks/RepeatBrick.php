<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class RepeatBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = 'Repeat _ times';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
