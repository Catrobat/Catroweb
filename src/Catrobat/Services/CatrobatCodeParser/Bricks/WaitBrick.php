<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WaitBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::WAIT_BRICK;
    $this->caption = 'Wait _ second(s)';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
