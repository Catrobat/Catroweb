<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoEV3MotorPlayToneBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_EV3_MOTOR_PLAY_TONE_BRICK;
    $this->caption = 'Play EV3 tone for _ seconds - Frequency: _ x100Hz - Volume: _ %';
    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
