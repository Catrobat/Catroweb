<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ArduinoSendDigitalValueBrick.
 */
class ArduinoSendDigitalValueBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::ARDUINO_SEND_DIGITAL_VALUE_BRICK;
    $this->caption = 'Set Arduino digital pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
