<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoNxtMotorMoveBrick.
 */
class LegoNxtMotorMoveBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_NXT_MOTOR_MOVE_BRICK;
    $this->caption = 'Set NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
