<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class RepeatUntilBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::REPEAT_UNTIL_BRICK;
    $this->caption = 'Repeat until _ is true';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
