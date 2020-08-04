<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class RaspiPwmBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RASPI_PWM_BRICK;
    $this->caption = 'Set Raspberry Pi PWM pin _ to _';
    $this->setImgFile(Constants::RASPI_BRICK_IMG);
  }
}
