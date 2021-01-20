<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LegoNxtMotorStopBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_MOTOR_STOP_BRICK;
    $this->caption = 'Stop NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
