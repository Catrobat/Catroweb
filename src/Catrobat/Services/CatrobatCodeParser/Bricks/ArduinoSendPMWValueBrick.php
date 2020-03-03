<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ArduinoSendPMWValueBrick.
 */
class ArduinoSendPMWValueBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::ARDUINO_SEND_PMW_VALUE_BRICK;
    $this->caption = 'Set Arduino PMW~ pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
