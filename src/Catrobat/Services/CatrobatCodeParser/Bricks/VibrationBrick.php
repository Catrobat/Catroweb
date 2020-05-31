<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class VibrationBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::VIBRATION_BRICK;
    $this->caption = 'Vibrate for _ second(s)';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
