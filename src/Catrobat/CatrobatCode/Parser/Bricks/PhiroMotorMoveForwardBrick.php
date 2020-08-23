<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PhiroMotorMoveForwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_FORWARD_BRICK;
    $this->caption = 'Move Phiro motor forward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
