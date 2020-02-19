<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoEV3MotorPlayToneBrick.
 */
class LegoEV3MotorPlayToneBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_PLAY_TONE_BRICK;
    $this->caption = 'Play EV3 tone for _ seconds - Frequency: _ x100Hz - Volume: _ %';
    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
