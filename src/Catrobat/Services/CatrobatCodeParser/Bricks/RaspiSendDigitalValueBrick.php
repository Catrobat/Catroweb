<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class RaspiSendDigitalValueBrick.
 */
class RaspiSendDigitalValueBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::RASPI_SEND_DIGITAL_VALUE_BRICK;
    $this->caption = 'Set Raspberry Pi pin _ to _';
    $this->setImgFile(Constants::RASPI_BRICK_IMG);
  }
}
