<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoNxtMotorTurnAngleBrick.
 */
class LegoNxtMotorTurnAngleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_NXT_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = 'Turn NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
