<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ArduinoSendPMWValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ARDUINO_SEND_PMW_VALUE_BRICK;
    $this->caption = 'Set Arduino PMW~ pin';
    $this->setImgFile(Constants::ARDUINO_BRICK_IMG);
  }
}
