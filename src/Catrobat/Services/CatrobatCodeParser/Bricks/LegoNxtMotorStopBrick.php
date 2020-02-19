<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoNxtMotorStopBrick.
 */
class LegoNxtMotorStopBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_NXT_MOTOR_STOP_BRICK;
    $this->caption = 'Stop NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
