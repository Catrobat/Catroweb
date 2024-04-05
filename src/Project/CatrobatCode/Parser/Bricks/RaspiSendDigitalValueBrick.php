<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class RaspiSendDigitalValueBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RASPI_SEND_DIGITAL_VALUE_BRICK;
    $this->caption = 'Set Raspberry Pi pin _ to _';
    $this->setImgFile(Constants::RASPI_BRICK_IMG);
  }
}
