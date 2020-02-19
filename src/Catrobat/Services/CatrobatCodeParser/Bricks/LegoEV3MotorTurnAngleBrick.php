<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LegoEV3MotorTurnAngleBrick.
 */
class LegoEV3MotorTurnAngleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = 'Turn EV3 motor _ by _°';

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
