<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PhiroMotorMoveBackwardBrick.
 */
class PhiroMotorMoveBackwardBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_BACKWARD_BRICK;
    $this->caption = 'Move Phiro motor backward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
