<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class BackgroundRequestBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::BACKGROUND_REQUEST_BRICK;
    $this->caption = 'Get image from _ and use as current background';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
