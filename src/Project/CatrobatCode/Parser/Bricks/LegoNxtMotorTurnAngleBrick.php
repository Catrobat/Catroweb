<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class LegoNxtMotorTurnAngleBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LEGO_NXT_MOTOR_TURN_ANGLE_BRICK;
    $this->caption = 'Turn NXT motor';
    $this->setImgFile(Constants::LEGO_NXT_BRICK_IMG);
  }
}
