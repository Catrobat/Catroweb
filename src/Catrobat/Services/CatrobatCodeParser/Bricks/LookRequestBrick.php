<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class LookRequestBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::LOOK_REQUEST_BRICK;
    $this->caption = 'Get image from _ and use as current look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
