<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PhiroMotorMoveBackwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_BACKWARD_BRICK;
    $this->caption = 'Move Phiro motor backward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
