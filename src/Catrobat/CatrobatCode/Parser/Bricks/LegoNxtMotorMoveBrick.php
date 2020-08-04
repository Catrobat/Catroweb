<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LegoNxtMotorMoveBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_MOTOR_MOVE_BRICK;
    $this->caption = 'Set NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
