<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class LegoEV3MotorTurnAngleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = 'Turn EV3 motor _ by _°';

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
