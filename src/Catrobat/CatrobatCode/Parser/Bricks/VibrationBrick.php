<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class VibrationBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::VIBRATION_BRICK;
    $this->caption = 'Vibrate for _ second(s)';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
