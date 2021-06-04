<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ResetTimerBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RESET_TIMER_BRICK;
    $this->caption = 'Reset timer';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
