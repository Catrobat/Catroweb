<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class OpenUrlBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::OPEN_URL_BRICK;
    $this->caption = 'Open _ in browser';
    $this->setImgFile(Constants::DEVICE_BRICK_IMG);
  }
}
