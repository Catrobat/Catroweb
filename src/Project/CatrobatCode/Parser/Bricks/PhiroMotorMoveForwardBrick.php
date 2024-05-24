<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroMotorMoveForwardBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_MOVE_FORWARD_BRICK;
    $this->caption = 'Move Phiro motor forward';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
