<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ArduinoSendDigitalValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ARDUINO_SEND_DIGITAL_VALUE_BRICK;
    $this->caption = 'Set Arduino digital pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
