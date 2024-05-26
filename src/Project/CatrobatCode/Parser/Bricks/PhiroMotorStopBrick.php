<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroMotorStopBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_STOP_BRICK;
    $this->caption = 'Stop Phiro motor';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
