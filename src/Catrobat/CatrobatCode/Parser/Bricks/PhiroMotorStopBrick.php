<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PhiroMotorStopBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_MOTOR_STOP_BRICK;
    $this->caption = 'Stop Phiro motor';
    $this->setImgFile(Constants::PHIRO_BRICK_IMG);
  }
}
