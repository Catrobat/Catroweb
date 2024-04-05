<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoEV3MotorTurnAngleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_EV3_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = 'Turn EV3 motor _ by _°';

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}
