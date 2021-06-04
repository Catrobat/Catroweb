<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class RaspiSendDigitalValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RASPI_SEND_DIGITAL_VALUE_BRICK;
    $this->caption = 'Set Raspberry Pi pin _ to _';
    $this->setImgFile(Constants::RASPI_BRICK_IMG);
  }
}
