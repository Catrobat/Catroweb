<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PhiroMotorMoveForwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_FORWARD_BRICK;
    $this->caption = 'Move Phiro motor forward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
