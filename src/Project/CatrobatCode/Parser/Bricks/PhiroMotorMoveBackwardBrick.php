<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroMotorMoveBackwardBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_BACKWARD_BRICK;
    $this->caption = 'Move Phiro motor backward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
