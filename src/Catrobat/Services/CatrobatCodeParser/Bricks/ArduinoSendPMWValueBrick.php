<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ArduinoSendPMWValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ARDUINO_SEND_PMW_VALUE_BRICK;
    $this->caption = 'Set Arduino PMW~ pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
