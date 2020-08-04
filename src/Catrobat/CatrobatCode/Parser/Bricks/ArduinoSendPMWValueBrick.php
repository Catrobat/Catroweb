<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ArduinoSendPMWValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ARDUINO_SEND_PMW_VALUE_BRICK;
    $this->caption = 'Set Arduino PMW~ pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
